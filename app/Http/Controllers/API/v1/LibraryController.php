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
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\Season;
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
     * Returns the authenticated user's library delta across every sync stream.
     */
    public function sync(GetLibrarySyncRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = auth()->user();
        $limit = (int) ($data['limit'] ?? 10000);
        $since = $data['since'] ?? [];
        $syncTime = now()->timestamp;

        $entries = $this->syncEntries($user, $since['entries'] ?? [], $limit, $syncTime);

        $attributes = ['syncTime' => $syncTime, 'streams' => ['entries' => $entries['stream']]];
        $trackables = [];

        foreach (UserLibraryKind::syncStreams() as $stream => $morphClass) {
            $result = $this->syncTrackables(
                $user,
                $morphClass,
                $since[$stream] ?? [],
                $limit,
                $entries['referencedIDs'][$morphClass] ?? [],
                $syncTime
            );

            $attributes['streams'][$stream] = $result['stream'];
            $trackables = array_merge($trackables, $result['rows']);
        }

        return JSONResult::success([
            'data' => [
                'attributes' => $attributes,
                'relationships' => [
                    'entries' => $entries['rows'],
                    'trackables' => $trackables,
                    'episodes' => $this->syncEpisodes($user),
                ],
            ],
        ])->withHeaders([
            'X-State-Version' => (string) $user->state_version,
            'Cache-Control' => 'private, no-cache',
        ]);
    }

    /**
     * Returns the user-state delta over `user_libraries`.
     *
     * @param User $user
     * @param array $since
     * @param int $limit
     * @param int $syncTime
     *
     * @return array
     */
    private function syncEntries(User $user, array $since, int $limit, int $syncTime): array
    {
        $libraryTable = UserLibrary::TABLE_NAME;
        $ratingsTable = MediaRating::TABLE_NAME;
        $favoritesTable = UserFavorite::TABLE_NAME;
        $remindersTable = UserReminder::TABLE_NAME;

        // Only `user_libraries` soft-deletes.
        $query = UserLibrary::leftJoin($ratingsTable, function ($join) use ($ratingsTable, $libraryTable) {
            $join->on($ratingsTable . '.user_id', '=', $libraryTable . '.user_id')
                ->on($ratingsTable . '.model_id', '=', $libraryTable . '.trackable_id')
                ->on($ratingsTable . '.model_type', '=', $libraryTable . '.trackable_type');
        })
            ->leftJoin($favoritesTable, function ($join) use ($favoritesTable, $libraryTable) {
                $join->on($favoritesTable . '.user_id', '=', $libraryTable . '.user_id')
                    ->on($favoritesTable . '.favorable_id', '=', $libraryTable . '.trackable_id')
                    ->on($favoritesTable . '.favorable_type', '=', $libraryTable . '.trackable_type');
            })
            ->leftJoin($remindersTable, function ($join) use ($remindersTable, $libraryTable) {
                $join->on($remindersTable . '.user_id', '=', $libraryTable . '.user_id')
                    ->on($remindersTable . '.remindable_id', '=', $libraryTable . '.trackable_id')
                    ->on($remindersTable . '.remindable_type', '=', $libraryTable . '.trackable_type');
            })
            ->where($libraryTable . '.user_id', '=', $user->id)
            ->whereIn($libraryTable . '.trackable_type', array_values(UserLibraryKind::syncStreams()))
            ->select([
                $libraryTable . '.*',
                $ratingsTable . '.id as rating_id',
                $ratingsTable . '.rating as rating_score',
                $ratingsTable . '.description as rating_description',
                $ratingsTable . '.is_spoiler as rating_is_spoiler',
                $ratingsTable . '.recommendation as rating_recommendation',
                $ratingsTable . '.created_at as rating_created_at',
                $ratingsTable . '.updated_at as rating_updated_at',
                $favoritesTable . '.id as favorite_id',
                $favoritesTable . '.created_at as favorited_at',
                $remindersTable . '.id as reminder_id',
                $remindersTable . '.created_at as reminded_at',
            ]);

        $cursor = $this->applyCursor($query, $libraryTable, $since, true);

        // Total rows past the cursor.
        $total = (clone $query)->count();

        // The loop below enforces `limit`.
        $rowsCursor = $query
            ->orderBy($libraryTable . '.updated_at')
            ->orderBy($libraryTable . '.id')
            ->lazy(500);

        $rows = [];
        $referencedIDs = [];
        $count = 0;
        $lastRow = null;

        foreach ($rowsCursor as $row) {
            if ($count < $limit) {
                $rows[] = $this->buildEntryRow($row);
                $lastRow = $row;

                if ($row->deleted_at === null) {
                    $referencedIDs[$row->trackable_type][] = $row->trackable_id;
                }
            }
            $count++;

            if ($count > $limit) {
                break;
            }
        }

        return [
            'stream' => [
                'hasMore' => $count > $limit,
                'total' => $total,
                'nextSince' => $this->nextCursor($lastRow, $cursor, $syncTime),
            ],
            'rows' => $rows,
            'referencedIDs' => $referencedIDs,
        ];
    }

    /**
     * Returns the catalog delta over one trackable table.
     *
     * @param User $user
     * @param string $morphClass
     * @param array $since
     * @param int $limit
     * @param array $referencedIDs
     * @param int $syncTime
     *
     * @return array
     */
    private function syncTrackables(User $user, string $morphClass, array $since, int $limit, array $referencedIDs, int $syncTime): array
    {
        $table = $morphClass::TABLE_NAME;
        $libraryTable = UserLibrary::TABLE_NAME;

        $query = $morphClass::withoutGlobalScopes()
            ->with(['translation', 'media', 'genres', 'status', 'mediaType', 'mediaStat'])
            ->whereIn($table . '.id', function ($sub) use ($libraryTable, $morphClass, $user) {
                $sub->select('trackable_id')
                    ->from($libraryTable)
                    ->where('user_id', '=', $user->id)
                    ->where('trackable_type', '=', $morphClass)
                    ->whereNull('deleted_at');
            });

        $cursor = $this->applyCursor($query, $table, $since, false);

        $total = (clone $query)->count();

        $models = $query
            ->orderBy($table . '.updated_at')
            ->orderBy($table . '.id')
            ->limit($limit + 1)
            ->get();

        $hasMore = $models->count() > $limit;
        $models = $models->take($limit);
        $lastRow = $models->last();

        // A trackable an entry has just started pointing at predates the cursor.
        $gapIDs = array_diff($referencedIDs, $models->pluck('id')->all());

        if (!empty($gapIDs)) {
            $models = $models->concat(
                $morphClass::withoutGlobalScopes()
                    ->with(['translation', 'media', 'genres', 'status', 'mediaType', 'mediaStat'])
                    ->whereIn($table . '.id', $gapIDs)
                    ->get()
            );
        }

        return [
            'stream' => [
                'hasMore' => $hasMore,
                'total' => $total,
                'nextSince' => $this->nextCursor($lastRow, $cursor, $syncTime),
            ],
            'rows' => $models->map(fn ($model) => $this->buildTrackableRow($model, $morphClass))->all(),
        ];
    }

    /**
     * Returns the episodes of the user's reminded shows inside the reminder window.
     *
     * @param User $user
     *
     * @return array
     */
    private function syncEpisodes(User $user): array
    {
        $remindedIDs = UserReminder::where('user_id', '=', $user->id)
            ->where('remindable_type', '=', Anime::class)
            ->pluck('remindable_id');

        if ($remindedIDs->isEmpty()) {
            return [];
        }

        $episodeTable = Episode::TABLE_NAME;
        $seasonTable = Season::TABLE_NAME;
        $days = (int) config('library.reminder_window_days', 14);

        return Episode::join($seasonTable, $seasonTable . '.id', '=', $episodeTable . '.season_id')
            ->whereNull($seasonTable . '.deleted_at')
            ->whereIn($seasonTable . '.anime_id', $remindedIDs)
            ->whereNotNull($episodeTable . '.started_at')
            ->whereBetween($episodeTable . '.started_at', [now(), now()->addDays($days)])
            ->with(['media'])
            ->select([
                $episodeTable . '.*',
                $seasonTable . '.anime_id as trackable_id',
                $seasonTable . '.number as season_number',
            ])
            ->orderBy($episodeTable . '.started_at')
            ->get()
            ->map(fn (Episode $episode) => [
                'id' => (string) $episode->public_id,
                'trackableID' => (string) $episode->trackable_id,
                'numberTotal' => (int) $episode->number_total,
                'number' => (int) $episode->number,
                'seasonNumber' => (int) $episode->season_number,
                'startedAt' => $episode->started_at?->timestamp,
                'bannerURL' => $episode->media->firstWhere('collection_name', '=', MediaCollection::Banner)?->getFullUrl(),
            ])
            ->all();
    }

    /**
     * Narrows the query to the rows past the given cursor.
     *
     * @param mixed $query
     * @param string $table
     * @param array $since
     * @param bool $withTombstones
     *
     * @return null|array
     */
    private function applyCursor(mixed $query, string $table, array $since, bool $withTombstones): ?array
    {
        if (empty($since['updated_at'])) {
            return null;
        }

        // A formatted string binding keeps microsecond precision.
        $sinceUpdatedAt = Carbon::parse($since['updated_at']);
        $boundary = $sinceUpdatedAt->format('Y-m-d H:i:s.u');
        $id = (int) ($since['id'] ?? 0);

        // A cursor older than the tombstone-retention horizon is stale.
        $horizon = now()->subDays((int) config('library.tombstone_retention_days', 90));
        $freshness = isset($since['synced_at'])
            ? Carbon::createFromTimestamp((int) $since['synced_at'])
            : $sinceUpdatedAt;

        if ($freshness->lt($horizon)) {
            throw new GoneHttpException(__('Your sync cursor has expired. Restart the sync without a cursor.'));
        }

        if ($withTombstones) {
            $query->withTrashed();
        }

        $query->where(function ($outer) use ($table, $boundary, $id) {
            $outer->where($table . '.updated_at', '>', $boundary)
                ->orWhere(function ($inner) use ($table, $boundary, $id) {
                    $inner->where($table . '.updated_at', '=', $boundary)
                        ->where($table . '.id', '>', $id);
                });
        });

        return ['updatedAt' => $boundary, 'id' => (string) $id];
    }

    /**
     * Returns the cursor the next round of a stream resumes from.
     *
     * @param mixed $lastRow
     * @param null|array $incoming
     * @param int $syncTime
     *
     * @return null|array
     */
    private function nextCursor(mixed $lastRow, ?array $incoming, int $syncTime): ?array
    {
        // `updatedAt` is opaque and only ever echoed back.
        if ($lastRow !== null) {
            return [
                'updatedAt' => Carbon::parse($lastRow->updated_at)->format('Y-m-d H:i:s.u'),
                'id' => (string) $lastRow->id,
                'syncedAt' => $syncTime,
            ];
        }

        if ($incoming === null) {
            return null;
        }

        return $incoming + ['syncedAt' => $syncTime];
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

        // A title the user has never tracked before is reminded unless it lands in a muted list.
        $addedModels = $models->reject(fn ($model) => $userLibraries->has($model->id));

        // A title already sitting in a muted list keeps whatever reminder the user gave it.
        $mutedModels = $models->reject(function ($model) use ($userLibraries) {
            $status = $userLibraries->get($model->id)?->status;
            return $status !== null && !UserLibraryStatus::enablesRemindersByDefault($status);
        });

        // Clearing `deleted_at` restores a soft-deleted row on re-add.
        DB::transaction(function () use ($records, $user, $mutedModels, $addedModels, $userLibraryStatus) {
            UserLibrary::upsert(
                $records,
                ['user_id', 'trackable_type', 'trackable_id'],
                ['status', 'started_at', 'ended_at', 'deleted_at', 'updated_at']
            );

            if (UserLibraryStatus::enablesRemindersByDefault($userLibraryStatus->value)) {
                $user->remind($addedModels);
            } else {
                $user->unremind($mutedModels);
            }

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
        $entries = $userLibraries->map(fn ($library) => $this->buildEntryRow($library))->all();

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
     * Projects a `UserLibrary` row into the entries-stream shape.
     *
     * @param UserLibrary $row
     * @return array
     */
    private function buildEntryRow(UserLibrary $row): array
    {
        $ratingID = $row->rating_id ?? null;
        $favoriteID = $row->favorite_id ?? null;
        $reminderID = $row->reminder_id ?? null;

        return [
            'id' => (string) $row->id,
            'kind' => UserLibraryKind::fromMorphClass($row->trackable_type),
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
                    'isSpoiler' => (bool) $row->rating_is_spoiler,
                    'recommendation' => isset($row->rating_recommendation) ? (int) $row->rating_recommendation : null,
                    'createdAt' => isset($row->rating_created_at) ? Carbon::parse($row->rating_created_at)->timestamp : null,
                    'updatedAt' => isset($row->rating_updated_at) ? Carbon::parse($row->rating_updated_at)->timestamp : null,
                ]
                : null,
        ];
    }

    /**
     * Projects a catalog model into the trackables-stream shape.
     *
     * @param mixed $trackable
     * @param string $morphClass
     * @return array
     */
    private function buildTrackableRow(mixed $trackable, string $morphClass): array
    {
        $poster = $trackable->media->firstWhere('collection_name', '=', MediaCollection::Poster);
        $banner = $trackable->media->firstWhere('collection_name', '=', MediaCollection::Banner);
        $airingDate = match ($morphClass) {
            Anime::class => $trackable->broadcast_date?->timestamp,
            Manga::class => $trackable->publication_date?->timestamp,
            default => null,
        };
        $scheduleDay = match ($morphClass) {
            Anime::class => $trackable->air_day,
            default => $trackable->publication_day,
        };
        $scheduleSeason = match ($morphClass) {
            Anime::class => $trackable->air_season,
            default => $trackable->publication_season,
        };
        $episodeCount = match ($morphClass) {
            Anime::class => $trackable->episode_count,
            Manga::class => $trackable->chapter_count,
            default => $trackable->edition_count,
        };
        $seasonCount = match ($morphClass) {
            Anime::class => $trackable->season_count,
            Manga::class => $trackable->volume_count,
            default => null,
        };
        $firstAired = $trackable->started_at ?? $trackable->published_at;
        $lastAired = $trackable->ended_at;

        return [
            'id' => (string) $trackable->id,
            'kind' => UserLibraryKind::fromMorphClass($morphClass),
            'slug' => $trackable->slug,
            'title' => $trackable->title,
            'sortTitle' => $this->normalizedSortTitle($trackable->title),
            'tagline' => $trackable->tagline,
            'posterURL' => $poster?->getFullUrl(),
            'posterBackgroundColor' => $poster?->getCustomProperty('background_color'),
            'bannerURL' => $banner?->getFullUrl(),
            'bannerBackgroundColor' => $banner?->getCustomProperty('background_color'),
            'genresLocalized' => $trackable->genres->pluck('name')->implode(', '),
            'mediaTypeID' => $trackable->media_type_id !== null ? (int) $trackable->media_type_id : null,
            'mediaTypeName' => $trackable->mediaType?->name,
            'statusID' => $trackable->status_id !== null ? (int) $trackable->status_id : null,
            'statusName' => $trackable->status?->name,
            'airingDate' => $airingDate,
            'durationCount' => $trackable->duration,
            'popularityRank' => $trackable->mediaStat?->rank_total,
            'publicRating' => $trackable->mediaStat?->rating_average !== null ? (float) $trackable->mediaStat->rating_average : null,
            'tvRatingID' => $trackable->tv_rating_id !== null ? (int) $trackable->tv_rating_id : null,
            'sourceID' => $trackable->source_id !== null ? (int) $trackable->source_id : null,
            'countryOfOrigin' => $trackable->country_id,
            'isNSFW' => $trackable->is_nsfw !== null ? (bool) $trackable->is_nsfw : null,
            'scheduleDay' => $scheduleDay?->value,
            'scheduleSeason' => $scheduleSeason?->value,
            'episodeCount' => $episodeCount !== null ? (int) $episodeCount : null,
            'seasonCount' => $seasonCount !== null ? (int) $seasonCount : null,
            'firstAired' => $firstAired ? Carbon::parse($firstAired)->timestamp : null,
            'lastAired' => $lastAired ? Carbon::parse($lastAired)->timestamp : null,
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
