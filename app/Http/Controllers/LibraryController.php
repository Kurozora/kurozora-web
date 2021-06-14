<?php

namespace App\Http\Controllers;

use App\Enums\MALImportBehavior;
use App\Models\Anime;
use App\Enums\UserLibraryStatus;
use App\Helpers\JSONResult;
use App\Http\Requests\AddToLibraryRequest;
use App\Http\Requests\DeleteFromLibraryRequest;
use App\Http\Requests\GetLibraryRequest;
use App\Http\Requests\MALImportRequest;
use App\Http\Requests\SearchLibraryRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Jobs\ProcessMALImport;
use App\Models\User;
use Auth;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class LibraryController extends Controller
{
    /**
     * Returns the authenticated user's library with the given status.
     *
     * @param GetLibraryRequest $request
     * @return JsonResponse
     */
    public function index(GetLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        /** @var User $user */
        $user = Auth::user();

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
     * Adds an Anime to the authenticated user's library
     *
     * @param AddToLibraryRequest $request
     * @return JsonResponse
     * @throws InvalidEnumKeyException
     */
    public function addLibrary(AddToLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $animeID = $data['anime_id'];

        // Get the authenticated user
        /** @var User $user */
        $user = Auth::user();

        // Get the Anime
        /** @var Anime $anime */
        $anime = Anime::findOrFail($animeID);

        // Get the status
        $userLibraryStatus = UserLibraryStatus::fromKey($data['status']);

        // Detach the current entry (if there is one)
        $user->library()->detach($anime);

        // Add a new library entry
        $user->library()->attach($anime, ['status' => $userLibraryStatus->value]);

        // Successful response
        return JSONResult::success([
            'data' => [
                'libraryStatus' => $userLibraryStatus->description,
                'isFavorited'   => $user->favoriteAnime()->where('anime_id', $animeID)->exists(),
                'isReminded'    => $user->userReminderAnime()->where('anime_id', $animeID)->exists()
            ]
        ]);
    }

    /**
     * Removes an Anime from the authenticated user's library
     *
     * @param DeleteFromLibraryRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function delLibrary(DeleteFromLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $animeID = $data['anime_id'];

        // Get the authenticated user
        /** @var User $user */
        $user = Auth::user();

        // Remove this Anime from their library if it can be found
        if ($user->library()->where('anime_id', $animeID)->count()) {
            $user->library()->detach($animeID);

            // Remove from favorites as you can't favorite and not have anime in library
            $user->favoriteAnime()->detach($animeID);
            // Remove from reminders as you can't remind and not have anime in library
            $user->reminderAnime()->detach($animeID);

            return JSONResult::success([
                'data' => [
                    'libraryStatus' => null,
                    'isFavorited'   => null,
                    'isReminded'    => null
                ]
            ]);
        }

        // The item could not be found
        throw new AuthorizationException('This item is not in your library.');
    }

    /**
     * Allows the authenticated user to upload a MAL export file to be imported.
     *
     * @param MALImportRequest $request
     * @return JsonResponse
     * @throws FileNotFoundException
     * @throws TooManyRequestsHttpException
     */
    function malImport(MALImportRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        /** @var User $user */
        $user = Auth::user();

        if (!$user->canDoMALImport()) {
            $cooldownDays = config('mal-import.cooldown_in_days');

            throw new TooManyRequestsHttpException($cooldownDays * 24 * 60 * 60, 'You can only perform a MAL import every ' . $cooldownDays . ' day(s).');
        }

        // Read XML file
        $xmlContent = File::get($data['file']->getRealPath());

        // Get import behavior
        $behavior = MALImportBehavior::fromValue((int) $data['behavior']);

        // Dispatch job
        dispatch(new ProcessMALImport($user, $xmlContent, $behavior));

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
     * @return JsonResponse
     */
    public function search(SearchLibraryRequest $request): JsonResponse
    {
        $searchQuery = $request->input('query');

        // Get the authenticated user
        /** @var User $user */
        $user = Auth::user();

        // Search for the anime
        $library = $user->library()
            ->search($searchQuery)->limit(Anime::MAX_SEARCH_RESULTS)->get();

        // Show response
        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($library)
        ]);
    }
}
