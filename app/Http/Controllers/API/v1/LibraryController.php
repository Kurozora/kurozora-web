<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\MediaCollection;
use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddToLibraryRequest;
use App\Http\Requests\ClearUserLibraryRequest;
use App\Http\Requests\DeleteFromLibraryRequest;
use App\Http\Requests\GetLibraryRequest;
use App\Http\Requests\GetLibrarySyncRequest;
use App\Http\Requests\LibraryImportRequest;
use App\Http\Requests\UpdateLibraryRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\GameResourceBasic;
use App\Http\Resources\LiteratureResourceBasic;
use App\Jobs\ProcessMALImport;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\User;
use App\Models\UserFavorite;
use App\Models\UserLibrary;
use App\Models\UserReminder;
use App\Scopes\IgnoreListScope;
use App\Traits\Controller\WithStateVersionETag;
use App\Traits\Model\Remindable;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class LibraryController extends Controller
{
    use WithStateVersionETag;

    /**
     * Returns the authenticated user's library with the given status.
     *
     *
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     * @throws ConnectionException
     */
    public function index(GetLibraryRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $kind = (int) ($data['kind'] ?? UserLibraryKind::Anime);

        // Get the library status
        if (is_numeric($data['status'])) {
            $userLibraryStatus = UserLibraryStatus::fromValue((int) $data['status']);
        } else {
            $userLibraryStatus = UserLibraryStatus::fromKey($data['status']);
        }

        $limit = (int) ($data['limit'] ?? 25);
        $page = max(1, (int) ($data['page'] ?? 1));
        $offset = isset($data['offset'])
            ? max(0, (int) $data['offset'])
            : ($page - 1) * $limit;

        $notModified = $this->returnIfNotModified($request, $user, [
            'kind' => $kind,
            'status' => $userLibraryStatus->value,
            'limit' => $limit,
            'offset' => $offset,
            'sort' => $request->query('sort'),
            'targetUserId' => $user->id,
        ]);
        if ($notModified !== null) {
            return $notModified;
        }
        $etag = $this->stateVersionETag($user, [
            'kind' => $kind,
            'status' => $userLibraryStatus->value,
            'limit' => $limit,
            'offset' => $offset,
            'sort' => $request->query('sort'),
            'targetUserId' => $user->id,
        ]);

        $page = max(1, (int) ($data['page'] ?? 1));

        // Get morph class
        $morphClass = match ($kind) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };

        $query = $user->whereTracked($morphClass)
            ->withoutGlobalScopes([IgnoreListScope::class])
            ->when(auth()->id() !== $user->id, function (Builder $query) {
                $query->where(UserLibrary::TABLE_NAME.'.is_hidden', '=', false);
            })
            ->sortViaRequest($request)
            ->with([
                'genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin', 'mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id],
                    ]);
                },
            ])
            ->wherePivot('status', '=', $userLibraryStatus->value)
            ->withExists([
                'favoriters as isFavorited' => function ($query) use ($morphClass, $user) {
                    $query->where('favorable_type', '=', $morphClass)
                        ->where('user_id', '=', $user->id);
                },
            ])
            ->when(in_array(Remindable::class, class_uses_recursive($morphClass)), function ($query) use ($morphClass, $user) {
                $query->withExists([
                    'reminderers as isReminded' => function ($query) use ($morphClass, $user) {
                        $query->where('remindable_type', '=', $morphClass)
                            ->where('user_id', '=', $user->id);
                    },
                ]);
            });

        $total = (clone $query)->count();
        $items = (clone $query)->skip($offset)->take($limit)->get();

        $nextOffset = $offset + $items->count();
        $nextPageURL = null;
        if ($items->count() === $limit && $nextOffset < $total) {
            $nextQuery = array_merge($request->query(), [
                'offset' => $nextOffset,
                'page' => $page + 1,
            ]);
            $nextPageURL = $request->getPathInfo().'?'.http_build_query($nextQuery);
        }

        // Get data collection
        $resourceData = match ($kind) {
            UserLibraryKind::Manga => ['literatures' => LiteratureResourceBasic::collection($items)],
            UserLibraryKind::Game => ['games' => GameResourceBasic::collection($items)],
            default => ['shows' => AnimeResourceBasic::collection($items)],
        };

        return JSONResult::success([
            'data' => $resourceData,
            'next' => $nextPageURL,
            'total' => $total,
        ])->withHeaders($this->stateVersionHeaders($etag, $user));
    }

    /**
     * Returns the authenticated user's combined library delta since the given cursor.
     *
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function sync(GetLibrarySyncRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = auth()->user();
        $limit = (int) ($data['limit'] ?? 10000);
        $since = $data['since'] ?? [];

        $libraryKind = UserLibraryKind::fromValue((int) $data['kind']);
        $morphClass = match ($libraryKind->value) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };

        $libraryTable = UserLibrary::TABLE_NAME;
        $ratingsTable = MediaRating::TABLE_NAME;
        $favoritesTable = UserFavorite::TABLE_NAME;
        $remindersTable = UserReminder::TABLE_NAME;

        // Only `user_libraries` soft-deletes; the joined tables don't need a filter.
        $query = UserLibrary::leftJoin($ratingsTable, function ($join) use ($ratingsTable, $libraryTable, $morphClass) {
            $join->on($ratingsTable . '.user_id', '=', $libraryTable . '.user_id')
                ->on($ratingsTable . '.model_id', '=', $libraryTable . '.trackable_id')
                ->where($ratingsTable . '.model_type', '=', $morphClass);
        })
            ->leftJoin($favoritesTable, function ($join) use ($favoritesTable, $libraryTable, $morphClass) {
                $join->on($favoritesTable . '.user_id', '=', $libraryTable . '.user_id')
                    ->on($favoritesTable . '.favorable_id', '=', $libraryTable . '.trackable_id')
                    ->where($favoritesTable . '.favorable_type', '=', $morphClass);
            })
            ->leftJoin($remindersTable, function ($join) use ($remindersTable, $libraryTable, $morphClass) {
                $join->on($remindersTable . '.user_id', '=', $libraryTable . '.user_id')
                    ->on($remindersTable . '.remindable_id', '=', $libraryTable . '.trackable_id')
                    ->where($remindersTable . '.remindable_type', '=', $morphClass);
            })
            ->where($libraryTable . '.user_id', '=', $user->id)
            ->where($libraryTable . '.trackable_type', '=', $morphClass)
            ->with(['trackable' => function ($q) {
                $q->withoutGlobalScopes()->with(['translation', 'media', 'genres', 'status', 'mediaType', 'mediaStat']);
            }])
            ->select([
                $libraryTable . '.*',
                $ratingsTable . '.id as rating_id',
                $ratingsTable . '.rating as rating_score',
                $ratingsTable . '.description as rating_description',
                $ratingsTable . '.created_at as rating_created_at',
                $ratingsTable . '.updated_at as rating_updated_at',
                $favoritesTable . '.id as favorite_id',
                $favoritesTable . '.created_at as favorited_at',
                $remindersTable . '.id as reminder_id',
                $remindersTable . '.created_at as reminded_at',
            ]);

        if (!empty($since['updated_at'])) {
            // Bind as a formatted string; a Carbon binding loses microsecond precision.
            $sinceUpdatedAt = Carbon::parse($since['updated_at']);
            $sinceBoundary = $sinceUpdatedAt->format('Y-m-d H:i:s.u');
            $sinceId = (int) ($since['id'] ?? 0);

            // A cursor older than the tombstone-retention horizon is stale.
            $horizon = now()->subDays((int) config('library.tombstone_retention_days', 90));
            $freshness = isset($since['synced_at'])
                ? Carbon::createFromTimestamp((int) $since['synced_at'])
                : $sinceUpdatedAt;
            if ($freshness->lt($horizon)) {
                throw new GoneHttpException(__('Your sync cursor has expired. Restart the sync without a cursor.'));
            }

            // Incremental sync surfaces tombstones.
            $query->withTrashed()
                ->where(function ($outer) use ($libraryTable, $sinceBoundary, $sinceId) {
                    $outer->where($libraryTable . '.updated_at', '>', $sinceBoundary)
                        ->orWhere(function ($inner) use ($libraryTable, $sinceBoundary, $sinceId) {
                            $inner->where($libraryTable . '.updated_at', '=', $sinceBoundary)
                                ->where($libraryTable . '.id', '>', $sinceId);
                        });
                });
        }
        // Initial sync — SoftDeletes scope already filters tombstones; pure waste to emit them.

        // Total rows past the cursor.
        $total = (clone $query)->count();

        // `lazy()` doesn't cap rows itself; the loop below enforces `limit` explicitly.
        $rowsCursor = $query
            ->orderBy($libraryTable . '.updated_at')
            ->orderBy($libraryTable . '.id')
            ->lazy(500);

        $libraries = [];
        $count = 0;
        $lastRow = null;

        foreach ($rowsCursor as $row) {
            if ($count < $limit) {
                $libraries[] = $this->buildSyncEntry($row, $morphClass);
                $lastRow = $row;
            }
            $count++;

            if ($count > $limit) {
                break;
            }
        }

        $hasMore = $count > $limit;
        $syncTime = now()->timestamp;
        $hasIncomingCursor = !empty($since['updated_at']);

        if ($lastRow !== null) {
            // `updatedAt` is opaque; never reinterpret it, only echo it back.
            $nextSince = [
                'updatedAt' => Carbon::parse($lastRow->updated_at)->format('Y-m-d H:i:s.u'),
                'id' => (string) $lastRow->id,
                'syncedAt' => $syncTime,
            ];
        } elseif ($hasIncomingCursor) {
            // Empty incremental delta — echo the same cursor back with a refreshed `syncedAt`.
            $nextSince = [
                'updatedAt' => $sinceBoundary,
                'id' => (string) $sinceId,
                'syncedAt' => $syncTime,
            ];
        } else {
            $nextSince = null;
        }

        return JSONResult::success([
            'data' => [
                'attributes' => [
                    'kind' => $libraryKind->value,
                    'syncTime' => $syncTime,
                    'hasMore' => $hasMore,
                    'total' => $total,
                    'nextSince' => $nextSince,
                ],
                'relationships' => [
                    'libraries' => $libraries,
                ],
            ],
        ])->withHeaders([
            'X-State-Version' => (string) $user->state_version,
            'Cache-Control' => 'private, no-cache',
        ]);
    }

    /**
     * Adds a model to the authenticated user's library
     *
     *
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     * @throws Throwable
     */
    public function create(AddToLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the library status
        $userLibraryStatus = is_numeric($data['status'])
            ? UserLibraryStatus::fromValue((int) $data['status'])
            : UserLibraryStatus::fromKey($data['status']);

        // Get the models
        $libraryKind = UserLibraryKind::fromValue((int) $data['kind']);
        $modelClass = match ($libraryKind->value) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };
        $modelIDs = $data['model_ids'] ?? [$data['model_id']];
        $models = $modelClass::withoutGlobalScopes()
            ->whereIn('id', $modelIDs)
            ->with(['translation', 'media', 'genres', 'status', 'mediaType', 'mediaStat'])
            ->get();

        if ($models->isEmpty()) {
            throw new ModelNotFoundException(__('No valid titles were found to add to your library.'));
        }

        // The raw upsert bypasses `$dateFormat`; format explicitly for microsecond precision.
        $modelType = $models->first()->getMorphClass();
        $now = now()->format('Y-m-d H:i:s.u');

        $userLibraries = $user->library()
            ->where('trackable_type', '=', $modelType)
            ->whereIn('trackable_id', $modelIDs)
            ->get()
            ->keyBy('trackable_id');

        $records = $models->map(function ($model) use ($user, $userLibraries, $userLibraryStatus, $modelType, $now) {
            $existingUserLibraryModel = $userLibraries->get($model->id) ?? new UserLibrary;
            $existingUserLibraryModel->updateStatus($userLibraryStatus->value);

            return [
                'user_id' => $user->id,
                'trackable_type' => $modelType,
                'trackable_id' => $model->id,
                'status' => $userLibraryStatus->value,
                'started_at' => $existingUserLibraryModel->started_at,
                'ended_at' => $existingUserLibraryModel->ended_at,
                'deleted_at' => null,
                'created_at' => $existingUserLibraryModel->created_at ?? $now,
                'updated_at' => $now,
            ];
        })->all();

        // Bulk upsert; clearing `deleted_at` restores a soft-deleted row on re-add.
        DB::transaction(function () use ($records, $user) {
            UserLibrary::upsert(
                $records,
                ['user_id', 'trackable_type', 'trackable_id'],
                ['status', 'started_at', 'ended_at', 'deleted_at', 'updated_at']
            );

            $user->bumpStateVersion();
        });

        // Fetch upserted models
        $userLibraries = $user->library()
            ->where('trackable_type', '=', $modelType)
            ->whereIn('trackable_id', $models->pluck('id'))
            ->get();

        // Map each trackable model to its library entry in memory
        $modelMap = $models->keyBy('id');
        foreach ($userLibraries as $library) {
            if (isset($modelMap[$library->trackable_id])) {
                $library->setRelation('trackable', $modelMap[$library->trackable_id]);
            }
        }

        // Make searchable
        $userLibraries->searchable();

        // Project each row into the LibrarySyncEntry shape for the response.
        $entries = $userLibraries->map(fn ($library) => $this->buildSyncEntry($library, $modelType))->all();

        // Successful response
        return JSONResult::success([
            'data' => [
                'attributes' => [
                    'status' => $userLibraryStatus->value,
                    'isFavorited' => false,
                    'isReminded' => false,
                    'isHidden' => false,
                    'rewatchCount' => 0,
                ],
                'relationships' => [
                    'libraries' => $entries,
                ],
            ],
        ]);
    }

    /**
     * Update a model in the authenticated user's library
     *
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(UpdateLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Authenticated user
        $user = auth()->user();

        // Determine library kind and morph type
        $libraryKind = UserLibraryKind::fromValue((int) $data['kind']);
        $modelType = match ($libraryKind->value) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };

        // Get the authenticated user
        $modelIDs = $data['model_ids'] ?? [$data['model_id']];
        $userLibraries = $user->library()
            ->where('trackable_type', '=', $modelType)
            ->whereIn('trackable_id', $modelIDs)
            ->get();

        if ($userLibraries->isEmpty()) {
            throw new AuthorizationException(__('None of the selected titles are in your library.'));
        }

        // The raw upsert bypasses `$dateFormat`; format explicitly for microsecond precision.
        $now = now()->format('Y-m-d H:i:s.u');

        $records = $userLibraries->map(fn ($library) => [
            'user_id' => $library->user_id,
            'trackable_type' => $library->trackable_type,
            'trackable_id' => $library->trackable_id,
            'status' => $library->status,
            'is_hidden' => $data['is_hidden'] ?? $library->is_hidden,
            'rewatch_count' => $data['rewatch_count'] ?? $library->rewatch_count,
            'updated_at' => $now,
        ])->all();

        DB::transaction(function () use ($records, $user) {
            UserLibrary::upsert(
                $records,
                ['user_id', 'trackable_type', 'trackable_id'],
                ['is_hidden', 'rewatch_count', 'updated_at']
            );

            $user->bumpStateVersion();
        });

        // Successful response; `relationships.libraries` stays empty here.
        return JSONResult::success([
            'data' => [
                'attributes' => [
                    'isHidden' => (bool) ($data['is_hidden'] ?? false),
                    'rewatchCount' => (int) ($data['rewatch_count'] ?? 0),
                ],
                'relationships' => [
                    'libraries' => [],
                ],
            ],
        ]);
    }

    /**
     * Removes a model from the authenticated user's library
     *
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function delete(DeleteFromLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the models
        $libraryKind = UserLibraryKind::fromValue((int) $data['kind']);
        $modelClass = match ($libraryKind->value) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };
        $modelIDs = $data['model_ids'] ?? [$data['model_id']];
        $models = $modelClass::withoutGlobalScopes()
            ->whereIn('id', $modelIDs)
            ->get();

        // Library rows are soft-deleted; favorites/reminders are still hard-deleted.
        DB::transaction(function () use ($user, $models) {
            $user->untrack($models);
            $user->unfavorite($models);
            $user->unremind($models);

            $user->bumpStateVersion();
        });

        return JSONResult::success([
            'data' => [
                'attributes' => [
                    'status' => null,
                    'isFavorited' => null,
                    'isReminded' => null,
                    'isHidden' => null,
                ],
                'relationships' => [
                    'libraries' => [],
                ],
            ],
        ]);
    }

    /**
     * Allows the authenticated user to upload a library export file to be imported.
     *
     *
     * @throws FileNotFoundException
     * @throws TooManyRequestsHttpException
     */
    public function import(LibraryImportRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the library to import to
        $libraryKind = UserLibraryKind::fromValue((int) $data['kind']);

        // Get whether user is in import cooldown period
        $isInImportCooldown = match ($libraryKind->value) {
            UserLibraryKind::Manga => ! $user->canDoMangaImport(),
            default => ! $user->canDoAnimeImport()
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
            $lastImportDateKey => now(),
        ]);

        return JSONResult::success([
            'message' => __('Your anime import request has been submitted. You will be notified once it has been processed!'),
        ]);
    }

    /**
     * Delete the user's library.
     *
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function clearLibrary(ClearUserLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Validate the password
        if (! Hash::check($data['password'], $user->password)) {
            throw new AuthorizationException(__('This password does not match our records.'));
        }

        // Get the user
        $libraryKind = UserLibraryKind::fromValue((int) $data['kind']);
        $type = match ($libraryKind->value) {
            UserLibraryKind::Anime => Anime::class,
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class
        };

        // Clear the specified library
        DB::transaction(function () use ($user, $type) {
            $user->clearLibrary($type);
            $user->clearFavorites($type);
            $user->clearReminders($type);
            $user->clearRatings($type);

            $user->bumpStateVersion();
        });

        return JSONResult::success();
    }

    /**
     * Projects a `UserLibrary` row into the `LibrarySyncEntry` shape.
     *
     * @param UserLibrary $row
     * @param string $morphClass
     * @return array
     */
    private function buildSyncEntry(UserLibrary $row, string $morphClass): array
    {
        $trackable = $row->trackable;
        $poster = $trackable?->media->firstWhere('collection_name', '=', MediaCollection::Poster);
        $banner = $trackable?->media->firstWhere('collection_name', '=', MediaCollection::Banner);
        $airingDate = match ($morphClass) {
            Anime::class => $trackable?->broadcast_date?->timestamp,
            Manga::class => $trackable?->publication_date?->timestamp,
            default => null,
        };

        $ratingID = $row->rating_id ?? null;
        $favoriteID = $row->favorite_id ?? null;
        $reminderID = $row->reminder_id ?? null;

        return [
            'id' => (string) $row->id,
            'trackableID' => (string) $row->trackable_id,
            'status' => (int) $row->status,
            'rewatchCount' => (int) $row->rewatch_count,
            'isHidden' => (bool) $row->is_hidden,
            'startedAt' => $row->started_at ? Carbon::parse($row->started_at)->timestamp : null,
            'endedAt' => $row->ended_at ? Carbon::parse($row->ended_at)->timestamp : null,
            'createdAt' => $row->created_at ? Carbon::parse($row->created_at)->timestamp : null,
            'updatedAt' => $row->updated_at ? Carbon::parse($row->updated_at)->timestamp : null,
            'deletedAt' => $row->deleted_at ? Carbon::parse($row->deleted_at)->timestamp : null,
            'isFavorited' => $favoriteID !== null,
            'favoritedAt' => isset($row->favorited_at) ? Carbon::parse($row->favorited_at)->timestamp : null,
            'isReminded' => $reminderID !== null,
            'remindedAt' => isset($row->reminded_at) ? Carbon::parse($row->reminded_at)->timestamp : null,
            'review' => $ratingID !== null
                ? [
                    'id' => (string) $ratingID,
                    'score' => (float) $row->rating_score,
                    'description' => $row->rating_description,
                    'createdAt' => isset($row->rating_created_at) ? Carbon::parse($row->rating_created_at)->timestamp : null,
                    'updatedAt' => isset($row->rating_updated_at) ? Carbon::parse($row->rating_updated_at)->timestamp : null,
                ]
                : null,
            'slug' => $trackable?->slug,
            'title' => $trackable?->title,
            'sortTitle' => $this->normalizedSortTitle($trackable?->title),
            'tagline' => $trackable?->tagline,
            'posterURL' => $poster?->getFullUrl(),
            'posterBackgroundColor' => $poster?->getCustomProperty('background_color'),
            'bannerURL' => $banner?->getFullUrl(),
            'bannerBackgroundColor' => $banner?->getCustomProperty('background_color'),
            'genresLocalized' => $trackable?->genres->pluck('name')->implode(', '),
            'mediaTypeName' => $trackable?->mediaType?->name,
            'statusName' => $trackable?->status?->name,
            'airingDate' => $airingDate,
            'durationCount' => $trackable?->duration,
            'popularityRank' => $trackable?->mediaStat?->rank_total,
            'publicRating' => $trackable?->mediaStat?->rating_average !== null ? (float) $trackable->mediaStat->rating_average : null,
        ];
    }

    /**
     * Returns a sort key derived from the title, lowercased with the leading English article stripped.
     *
     * @param null|string $title
     * @return null|string
     */
    private function normalizedSortTitle(?string $title): ?string
    {
        if ($title === null) {
            return null;
        }

        $normalized = mb_strtolower(trim($title));
        $normalized = preg_replace('/^(the|a|an)\s+/u', '', $normalized);

        return $normalized;
    }
}
