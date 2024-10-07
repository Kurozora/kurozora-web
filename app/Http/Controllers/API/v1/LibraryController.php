<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddToLibraryRequest;
use App\Http\Requests\ClearUserLibraryRequest;
use App\Http\Requests\DeleteFromLibraryRequest;
use App\Http\Requests\GetLibraryRequest;
use App\Http\Requests\LibraryImportRequest;
use App\Http\Requests\UpdateLibraryRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\GameResourceBasic;
use App\Http\Resources\LiteratureResourceBasic;
use App\Jobs\ProcessMALImport;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\User;
use App\Models\UserLibrary;
use App\Scopes\IgnoreListScope;
use App\Traits\Model\Remindable;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class LibraryController extends Controller
{
    /**
     * Returns the authenticated user's library with the given status.
     *
     * @param GetLibraryRequest $request
     * @param User              $user
     *
     * @return JsonResponse
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function index(GetLibraryRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $library = (int) ($data['library'] ?? UserLibraryKind::Anime);

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
            ->withoutGlobalScopes([IgnoreListScope::class])
            ->when(auth()->user() !== $user, function (Builder $query) use ($user) {
                $query->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
            })
            ->sortViaRequest($request)
            ->with([
                'genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating', 'country_of_origin', 'mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id]
                    ]);
                }
            ])
            ->wherePivot('status', '=', $userLibraryStatus->value)
            ->withExists([
                'favoriters as isFavorited' => function ($query) use ($morphClass, $user) {
                    $query->where('favorable_type', '=', $morphClass)
                        ->where('user_id', '=', $user->id);
                },
            ])
            ->when(in_array(Remindable::class, class_uses_recursive($morphClass)), function ($query) use ($morphClass, $user) {
                // Add your logic here if the trait is used
                $query->withExists([
                    'reminderers as isReminded' => function ($query) use ($morphClass, $user) {
                        $query->where('remindable_type', '=', $morphClass)
                            ->where('user_id', '=', $user->id);
                    },
                ]);
            })
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $model->nextPageUrl() ?? '');

        // Get data collection
        $data = match ($library) {
            UserLibraryKind::Manga => ['literatures' => LiteratureResourceBasic::collection($model)],
            UserLibraryKind::Game => ['games' => GameResourceBasic::collection($model)],
            default => ['shows' => AnimeResourceBasic::collection($model)],
        };

        return JSONResult::success([
            'data' => $data,
            'next' => empty($nextPageURL) ? null : $nextPageURL,
            'total' => $model->total()
        ]);
    }

    /**
     * Adds a model to the authenticated user's library
     *
     * @param AddToLibraryRequest $request
     *
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
            $model = Anime::withoutGlobalScopes()
                ->findOrFail($modelID);
        } else {
            $modelID = $data['model_id'];
            $libraryKind = UserLibraryKind::fromValue((int) $data['library']);
            $model = match ($libraryKind->value) {
                UserLibraryKind::Manga => Manga::withoutGlobalScopes()
                    ->findOrFail($modelID),
                UserLibraryKind::Game => Game::withoutGlobalScopes()
                    ->findOrFail($modelID),
                default => Anime::withoutGlobalScopes()
                    ->findOrFail($modelID),
            };
        }

        // Update or create the user library entry.
        UserLibrary::withoutSyncingToSearch(function () use ($userLibraryStatus, $model, $user) {
            $userLibrary = UserLibrary::updateOrCreate([
                'user_id' => $user->id,
                'trackable_type' => $model->getMorphClass(),
                'trackable_id' => $model->id,
            ], [
                'status' => $userLibraryStatus->value,
            ]);

            $userLibrary->setRelation('trackable', $model);

            $userLibrary->searchable();
        });

        // Successful response
        return JSONResult::success([
            'data' => [
                'status' => $userLibraryStatus->value,
                'isFavorited' => false,
                'isReminded' => false,
                'isHidden' => false,
                'rewatchCount' => 0,
            ]
        ]);
    }

    /**
     * Update a model in the authenticated user's library
     *
     * @param UpdateLibraryRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(UpdateLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the model
        $modelID = $data['model_id'];
        $libraryKind = UserLibraryKind::fromValue((int) $data['library']);
        $modelType = match ($libraryKind->value) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };

        // Get the authenticated user
        $user = auth()->user();
        $library = $user->library()
            ->firstWhere([
                ['trackable_type', '=', $modelType],
                ['trackable_id', '=', $modelID],
            ]);

        if (!$library) {
            // The item could not be found
            throw new AuthorizationException(__('":x" is not in your library.', ['x' => $data['model_id']]));
        }

        // Update hidden status
        if ($request->has('is_hidden')) {
            $library->is_hidden = $data['is_hidden'];
        }

        // Update rewatch count
        if ($request->has('rewatch_count')) {
            $library->rewatch_count = $data['rewatch_count'];
        }

        // Save changes
        $library->save();

        // Successful response
        return JSONResult::success([
            'data' => [
                'isHidden' => $library->is_hidden,
                'rewatchCount' => $library->rewatch_count,
            ],
        ]);
    }

    /**
     * Removes a model from the authenticated user's library
     *
     * @param DeleteFromLibraryRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function delete(DeleteFromLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the model
        if (!empty($data['anime_id'])) {
            $modelID = $data['anime_id'];
            $model = Anime::withoutGlobalScopes()
                ->findOrFail($modelID);
        } else {
            $modelID = $data['model_id'];
            $libraryKind = UserLibraryKind::fromValue((int) $data['library']);
            $model = match ($libraryKind->value) {
                UserLibraryKind::Manga => Manga::withoutGlobalScopes()
                    ->findOrFail($modelID),
                UserLibraryKind::Game => Game::withoutGlobalScopes()
                    ->findOrFail($modelID),
                default => Anime::withoutGlobalScopes()
                    ->findOrFail($modelID),
            };
        }

        // Get the authenticated user
        $user = auth()->user();
        $hasNotTracked = $user->hasNotTracked($model);

        if ($hasNotTracked) {
            // The item could not be found
            throw new AuthorizationException(__('":x" is not in your library.', ['x' => $model->title]));
        }

        // Remove this Anime from their library if it can be found
        $user->untrack($model);

        // Remove from favorites as you can't favorite and not have the anime in library
        $user->unfavorite($model);

        // Remove from reminders as you can't be reminded and not have the anime in library
        $user->unremind($model);

        return JSONResult::success([
            'data' => [
                'libraryStatus' => null,
                'status' => null,
                'isFavorited' => null,
                'isReminded' => null,
                'isHidden' => null,
                'watchCount' => null,
            ]
        ]);
    }

    /**
     * Allows the authenticated user to upload a library export file to be imported.
     *
     * @param LibraryImportRequest $request
     *
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
            UserLibraryKind::Manga => 'manga_imported_at',
            default => 'anime_imported_at',
        };

        $user->update([
            $lastImportDateKey => now()
        ]);

        return JSONResult::success([
            'message' => __('Your anime import request has been submitted. You will be notified once it has been processed!')
        ]);
    }

    /**
     * Delete the user's library.
     *
     * @param ClearUserLibraryRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function clearLibrary(ClearUserLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Validate the password
        if (!Hash::check($data['password'], $user->password)) {
            throw new AuthorizationException(__('This password does not match our records.'));
        }

        // Get the user
        $libraryKind = UserLibraryKind::fromValue((int) $data['library']);
        $type = match ($libraryKind->value) {
            UserLibraryKind::Anime => Anime::class,
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class
        };

        // Clear the specified library
        $user->clearLibrary($type);
        $user->clearFavorites($type);
        $user->clearReminders($type);
        $user->clearRatings($type);

        return JSONResult::success();
    }
}
