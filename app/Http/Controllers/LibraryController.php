<?php

namespace App\Http\Controllers;

use App\Anime;
use App\Enums\UserLibraryStatus;
use App\Helpers\JSONResult;
use App\Http\Requests\AddToLibraryRequest;
use App\Http\Requests\DeleteFromLibraryRequest;
use App\Http\Requests\GetLibraryRequest;
use App\Http\Requests\MALImportRequest;
use App\Http\Requests\SearchLibraryRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Jobs\ProcessMALImport;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class LibraryController extends Controller
{
    /**
     * Gets the user's library depending on the status
     *
     * @param GetLibraryRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function getLibrary(GetLibraryRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get the status
        $foundStatus = UserLibraryStatus::getValue($data['status']);

        // Retrieve the Anime from the user's library with the correct status
        $anime = $user->library()
            ->sortViaRequest($request)
            ->wherePivot('status', $foundStatus)
            ->get();

        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($anime)
        ]);
    }

    /**
     * Adds an Anime to the user's library
     *
     * @param AddToLibraryRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function addLibrary(AddToLibraryRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $animeID = $data['anime_id'];

        // Get the Anime
        /** @var Anime $anime */
        $anime = Anime::findOrFail($animeID);

        // Get the status
        $foundStatus = UserLibraryStatus::getValue($data['status']);

        // Detach the current entry (if there is one)
        $user->library()->detach($anime);

        // Add a new library entry
        $user->library()->attach($anime, ['status' => $foundStatus]);

        // Successful response
        return JSONResult::success([
            'data' => [
                'libraryStatus' => $data['status'],
                'isFavorited'   => $user->favoriteAnime()->where('anime_id', $animeID)->exists(),
                'isReminded'    => $user->userReminderAnime()->where('anime_id', $animeID)->exists()
            ]
        ]);
    }

    /**
     * Removes an Anime from the user's library
     *
     * @param DeleteFromLibraryRequest $request
     * @param User $user
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function delLibrary(DeleteFromLibraryRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $animeID = $data['anime_id'];

        // Remove this Anime from their library if it can be found
        if($user->library()->where('anime_id', $animeID)->count()) {
            $user->library()->detach($animeID);

            // Remove from favorites as you can't favorite and not have anime in library
            $user->favoriteAnime()->detach($animeID);
            // Remove from reminders as you can't remind and not have anime in library
            $user->reminderAnime()->detach($animeID);

            return JSONResult::success([
                'data' => [
                    'libraryStatus' => $data['status'],
                    'isFavorited'   => null,
                    'isReminded'    => null
                ]
            ]);
        }

        // The item could not be found
        throw new AuthorizationException('This item is not in your library.');
    }

    /**
     * Allows the user to upload a MAL export file to be imported.
     *
     * @param MALImportRequest $request
     * @param User $user
     * @return JsonResponse
     * @throws FileNotFoundException
     * @throws TooManyRequestsHttpException
     */
    function malImport(MALImportRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if(!$user->canDoMALImport()) {
            $cooldownDays = config('mal-import.cooldown_in_days');

            throw new TooManyRequestsHttpException($cooldownDays * 24 * 60 * 60, 'You can only perform a MAL import every ' . $cooldownDays . ' day(s).');
        }

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

    /**
     * Retrieves user library search results
     *
     * @param SearchLibraryRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function search(SearchLibraryRequest $request, User $user): JsonResponse
    {
        $searchQuery = $request->input('query');

        // Search for the anime
        $library = $user->library()
            ->search($searchQuery)->limit(Anime::MAX_SEARCH_RESULTS)->get();

        // Show response
        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($library)
        ]);
    }
}
