<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryStatus;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddToLibraryRequest;
use App\Http\Requests\DeleteFromLibraryRequest;
use App\Http\Requests\GetLibraryRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Jobs\ProcessMALImport;
use App\Models\Anime;
use App\Models\UserLibrary;
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
        $user = auth()->user();

        // Get the status
        $foundStatus = UserLibraryStatus::getValue($data['status']);

        // Retrieve the Anime from the user's library with the correct status
        $anime = $user->library()
            ->sortViaRequest($request)
            ->wherePivot('status', $foundStatus)
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds an Anime to the authenticated user's library
     *
     * @param AddToLibraryRequest $request
     * @return JsonResponse
     * @throws InvalidEnumKeyException
     */
    public function create(AddToLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $animeID = $data['anime_id'];

        // Get the authenticated user
        $user = auth()->user();

        // Get the Anime
        $anime = Anime::findOrFail($animeID);

        // Get the status, and decide the end_date
        $userLibraryStatus = UserLibraryStatus::fromKey($data['status']);
        $endDate = match ($userLibraryStatus->value) {
            UserLibraryStatus::Completed => now(),
            default => null
        };

        // Update or create the user library entry
        UserLibrary::updateOrCreate([
            'user_id'   => $user->id,
            'anime_id'  => $anime->id,
        ], [
            'status' => $userLibraryStatus->value,
            'end_date' => $endDate
        ]);

        // Successful response
        return JSONResult::success([
            'data' => [
                'libraryStatus' => $userLibraryStatus->description,
                'isFavorited'   => $user->favorite_anime()->where('anime_id', $animeID)->exists(),
                'isReminded'    => $user->user_reminder_anime()->where('anime_id', $animeID)->exists()
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
    public function delete(DeleteFromLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $animeID = $data['anime_id'];

        // Get the authenticated user
        $user = auth()->user();

        // Remove this Anime from their library if it can be found
        if ($user->library()->where('anime_id', $animeID)->count()) {
            $user->library()->detach($animeID);

            // Remove from favorites as you can't favorite and not have anime in library
            $user->favorite_anime()->detach($animeID);
            // Remove from reminders as you can't remind and not have anime in library
            $user->reminder_anime()->detach($animeID);

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
     * Allows the authenticated user to upload an anime export file to be imported.
     *
     * @param ImportRequest $request
     * @return JsonResponse
     * @throws FileNotFoundException
     * @throws TooManyRequestsHttpException
     */
    function animeImport(ImportRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        if (!$user->canDoAnimeImport()) {
            $cooldownDays = config('import.cooldown_in_days');

            throw new TooManyRequestsHttpException($cooldownDays * 24 * 60 * 60, __('You can only perform an anime import every :x day(s).', ['x' => $cooldownDays]));
        }

        // Read XML file
        $xmlContent = File::get($data['file']->getRealPath());

        // Get the import service
        $importService = ImportService::fromValue((int) $data['service'] ?? 0);

        // Get import behavior
        $importBehavior = ImportBehavior::fromValue((int) $data['behavior']);

        // Dispatch job
        switch ($importService->value) {
            case ImportService::MAL:
            case ImportService::Kitsu:
                dispatch(new ProcessMALImport($user, $xmlContent, $importService, $importBehavior));
                break;
        }

        // Update last anime import date for user
        $user->update([
          'last_anime_import_at' => now()
        ]);

        return JSONResult::success([
            'message' => 'Your anime import request has been submitted. You will be notified once it has been processed!'
        ]);
    }
}
