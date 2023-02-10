<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddToLibraryRequest;
use App\Http\Requests\DeleteFromLibraryRequest;
use App\Http\Requests\GetLibraryRequest;
use App\Http\Requests\LibraryImportRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\GameResourceBasic;
use App\Http\Resources\LiteratureResourceBasic;
use App\Jobs\ProcessMALImport;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\UserLibrary;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
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
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function index(GetLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the library status
        if (is_numeric($data['status'])) {
            $userLibraryStatus = UserLibraryStatus::fromValue((int) $data['status']);
        } else {
            $userLibraryStatus = UserLibraryStatus::fromKey($data['status']);
        }

        // Get morph class
        $morphClass = match ((int) ($data['library'] ?? UserLibraryKind::Anime)) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };

        // Retrieve the model from the user's library with the correct status
        $model = $user->whereTracked($morphClass)
            ->sortViaRequest($request)
            ->wherePivot('status', '=', $userLibraryStatus->value)
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $model->nextPageUrl());

        // Get data collection
        $data = match ((int) ($data['library'] ?? UserLibraryKind::Anime)) {
            UserLibraryKind::Manga => ['literatures' => LiteratureResourceBasic::collection($model)],
            UserLibraryKind::Game => ['games' => GameResourceBasic::collection($model)],
            default => ['shows' => AnimeResourceBasic::collection($model)],
        };

        return JSONResult::success([
            'data' => $data,
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds an Anime to the authenticated user's library
     *
     * @param AddToLibraryRequest $request
     * @return JsonResponse
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function create(AddToLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the library status
        if (is_numeric($data['status'])) {
            $userLibraryStatus = UserLibraryStatus::fromValue((int) $data['status']);
        } else {
            $userLibraryStatus = UserLibraryStatus::fromKey($data['status']);
        }

        // Get the model
        if (!empty($data['anime_id'])) {
            $modelID = $data['anime_id'];
            $model = Anime::findOrFail($modelID);
        } else {
            $modelID = $data['model_id'];
            $libraryKind = UserLibraryKind::fromValue((int) $data['library']);
            $model = match ($libraryKind->value) {
                UserLibraryKind::Manga  => Manga::findOrFail($modelID),
                UserLibraryKind::Game   => Game::findOrFail($modelID),
                default                 => Anime::findOrFail($modelID),
            };
        }

        // Decide if the tracking ended
        $endedAt = match ($userLibraryStatus->value) {
            UserLibraryStatus::Completed => now(),
            default => null
        };

        // Update or create the user library entry
        UserLibrary::updateOrCreate([
            'user_id' => $user->id,
            'trackable_type' => $model->getMorphClass(),
            'trackable_id' => $model->id,
        ], [
            'status' => $userLibraryStatus->value,
            'ended_at' => $endedAt
        ]);

        // Decide the value of isReminded
        $isReminded = $model->getMorphClass() == Anime::class ? $user->user_reminder_anime()->where('anime_id', $modelID)->exists() : false;

        // Successful response
        return JSONResult::success([
            'data' => [
                'libraryStatus' => $userLibraryStatus->value,
                'isFavorited'   => $user->hasFavorited($model),
                'isReminded'    => $isReminded
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

        // Get the model
        if (!empty($data['anime_id'])) {
            $modelID = $data['anime_id'];
            $model = Anime::findOrFail($modelID);
        } else {
            $modelID = $data['model_id'];
            $libraryKind = UserLibraryKind::fromValue((int) $data['library']);
            $model = match ($libraryKind->value) {
                UserLibraryKind::Manga  => Manga::findOrFail($modelID),
                UserLibraryKind::Game   => Game::findOrFail($modelID),
                default                 => Anime::findOrFail($modelID),
            };
        }

        // Get the authenticated user
        $user = auth()->user();
        $hasNotTracked = $user->hasNotTracked($model);

        if ($hasNotTracked) {
            // The item could not be found
            throw new AuthorizationException(__(':x is not in your library.', ['x' => $model->title]));
        }

        // Remove this Anime from their library if it can be found
        $user->untrack($model);

        // Remove from favorites as you can't favorite and not have the anime in library
        $user->unfavorite($model);

        // Remove from reminders as you can't be reminded and not have the anime in library
        match ($libraryKind?->value ?? UserLibraryKind::Anime) {
            UserLibraryKind::Anime  => $user->reminderAnime()->detach($modelID),
            default => null
        };

        return JSONResult::success([
            'data' => [
                'libraryStatus' => null,
                'isFavorited'   => null,
                'isReminded'    => null
            ]
        ]);
    }

    /**
     * Allows the authenticated user to upload a library export file to be imported.
     *
     * @param LibraryImportRequest $request
     * @return JsonResponse
     * @throws FileNotFoundException
     * @throws TooManyRequestsHttpException
     */
    function import(LibraryImportRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the library to import to
        $libraryKind = UserLibraryKind::fromValue((int) $data['library']);

        // Get the authenticated user
        $user = auth()->user();

        // Get whether user is in import cooldown period
        $isInImportCooldown = match ($libraryKind->value) {
            UserLibraryKind::Manga => !$user->canDoMangaImport(),
            default => !$user->canDoAnimeImport()
        };

        if ($isInImportCooldown) {
            $cooldownDays = config('import.cooldown_in_days');

            throw match ($libraryKind->value) {
                UserLibraryKind::Manga => new TooManyRequestsHttpException($cooldownDays * 24 * 60 * 60, __('You can only perform a manga import every :x day(s).', ['x' => $cooldownDays])),
                UserLibraryKind::Game => new TooManyRequestsHttpException($cooldownDays * 24 * 60 * 60, __('You can only perform a game import every :x day(s).', ['x' => $cooldownDays])),
                default => new TooManyRequestsHttpException($cooldownDays * 24 * 60 * 60, __('You can only perform an anime import every :x day(s).', ['x' => $cooldownDays])),
            };
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
                dispatch(new ProcessMALImport($user, $xmlContent, $libraryKind, $importService, $importBehavior));
                break;
            default:
                break;
        }

        // Update last library import date for user
        $lastImportDateKey = match ($libraryKind->value) {
            UserLibraryKind::Manga => 'last_manga_import_at',
            default => 'last_anime_import_at',
        };

        $user->update([
            $lastImportDateKey => now()
        ]);

        return JSONResult::success([
            'message' => __('Your anime import request has been submitted. You will be notified once it has been processed!')
        ]);
    }
}
