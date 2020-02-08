<?php

namespace App\Http\Controllers;

use App\Anime;
use App\Enums\UserLibraryStatus;
use App\Helpers\JSONResult;
use App\Http\Requests\AddToLibrary;
use App\Http\Requests\DeleteFromLibrary;
use App\Http\Requests\GetLibrary;
use App\Http\Requests\MALImport;
use App\Http\Resources\AnimeResource;
use App\Jobs\ProcessMALImport;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class LibraryController extends Controller
{
    /**
     * Gets the user's library depending on the status
     *
     * @param GetLibrary $request
     * @param User $user
     * @return JsonResponse
     */
    public function getLibrary(GetLibrary $request, User $user) {
        $data = $request->validated();

        // Get the status
        $foundStatus = UserLibraryStatus::getValue($data['status']);

        // Retrieve the Anime from the user's library with the correct status
        $anime = $user->library()
            ->sortViaRequest($request)
            ->wherePivot('status', $foundStatus)
            ->get();

        return JSONResult::success([
            'anime' => AnimeResource::collection($anime)
        ]);
    }

    /**
     * Adds an Anime to the user's library
     *
     * @param AddToLibrary $request
     * @param User $user
     * @return JsonResponse
     */
    public function addLibrary(AddToLibrary $request, User $user) {
        $data = $request->validated();

        // Get the Anime
        /** @var Anime $anime */
        $anime = Anime::find($data['anime_id']);

        // Get the status
        $foundStatus = UserLibraryStatus::getValue($data['status']);

        // Detach the current entry (if there is one)
        $user->library()->detach($anime);

        // Add a new library entry
        $user->library()->attach($anime, ['status' => $foundStatus]);

        // Successful response
        return JSONResult::success();
    }

    /**
     * Removes an Anime from the user's library
     *
     * @param DeleteFromLibrary $request
     * @param User $user
     * @return JsonResponse
     */
    public function delLibrary(DeleteFromLibrary $request, User $user) {
        $data = $request->validated();

        // Remove this Anime from their library if it can be found
        if($user->library()->where('anime_id', $data['anime_id'])->count()) {
            $user->library()->detach($data['anime_id']);

            return JSONResult::success();
        }

        // The item could not be found
        return JSONResult::error('This item is not in your library.');
    }

    /**
     * Allows the user to upload a MAL export file to be imported.
     *
     * @param MALImport $request
     * @param User $user
     * @return JsonResponse
     */
    function malImport(MALImport $request, User $user) {
        $data = $request->validated();

        if(!$user->canDoMALImport())
            return JSONResult::error('Oops! You can only perform a MAL import every ' . config('mal-import.cooldown_in_days') . ' day(s).', 491812);

        // Read XML file
        $xmlContent = File::get($data['file']->getRealPath());

        // Dispatch job
        dispatch(new ProcessMALImport($user, $xmlContent, $data['behavior']));

        // Update last MAL import date for user
        $user->last_mal_import_at = now();
        $user->save();

        return JSONResult::success([
            'message' => 'Your MAL import request has been submitted. You will be notified once it has been processed!'
        ]);
    }
}
