<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\BrowseSeasonKind;
use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Events\ModelViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetBrowseSeasonRequest;
use App\Http\Requests\GetIndexRequest;
use App\Http\Requests\GetMediaSongsRequest;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\RateModelRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\GameCastResourceIdentity;
use App\Http\Resources\GameResource;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\MediaRelatedResource;
use App\Http\Resources\MediaSongResource;
use App\Http\Resources\MediaStaffResource;
use App\Http\Resources\StudioResource;
use App\Models\Game;
use App\Models\MediaRelation;
use App\Traits\Controller\WithCatalogCacheHeaders;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Routing\Redirector;

class GameController extends Controller
{
    use WithCatalogCacheHeaders;

    /**
     * Returns the games index.
     *
     * @param GetIndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws BindingResolutionException
     */
    public function index(GetIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['ids'])) {
            return $this->views($request);
        } else {
            // Override parameters
            $request->merge([
                'scope' => SearchScope::Kurozora,
                'types' => [
                    SearchType::Games
                ]
            ]);

            // Convert request type
            $app = app();
            $searchRequest = SearchRequest::createFrom($request)
                ->setContainer($app) // Necessary or validation fails (validate on null)
                ->setRedirector($app->make(Redirector::class)); // Necessary or validation failure fails (422)
            $searchRequest->validateResolved(); // Necessary for preparing for validation

            return (new SearchController())
                ->index($searchRequest);
        }
    }

    /**
     * Returns detailed information of a game.
     *
     * @param Request $request
     * @param game    $game
     *
     * @return JsonResponse
     */
    public function view(Request $request, Game $game): JsonResponse
    {
        $includeInput = $request->input('include');
        $includes = is_string($includeInput) ? explode(',', $includeInput) : (is_array($includeInput) ? $includeInput : []);
        sort($includes);

        $fingerprint = [
            'kind' => 'game',
            'publicId' => $game->public_id,
            'updatedAt' => optional($game->updated_at)->toIso8601String(),
            'locale' => app()->getLocale(),
            'tvRating' => (int) $request->attributes->get('tvRating', 4),
            'include' => $includes,
        ];

        $notModified = $this->returnIfNotModifiedCatalog($request, $fingerprint);
        if ($notModified !== null) {
            return $notModified;
        }

        // Call the ModelViewed event
        ModelViewed::dispatch($game, $request->ip());

        $user = auth()->user();

        $game->load(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
            ->when($user, function ($query, $user) use ($game) {
                $game->load(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id]
                    ]);
                }, 'library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }])
                    ->loadExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }
                    ]);
            });

        $includeArray = [];
        if ($includeInput = $request->input('include')) {
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            foreach ($includes as $include) {
                switch ($include) {
                    case 'cast':
                        $includeArray['cast'] = function ($query) {
                            $query->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'characters':
                        $includeArray['characters'] = function ($query) {
                            $query->with(['media', 'translation'])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-shows':
                        $includeArray['animeRelations'] = function ($query) use ($game) {
                            $query->with([
                                'related' => function ($query) use ($game) {
                                    $game->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-literatures':
                        $includeArray['mangaRelations'] = function ($query) use ($game) {
                            $query->with([
                                'related' => function ($query) use ($game) {
                                    $game->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-games':
                        $includeArray['gameRelations'] = function ($query) use ($game) {
                            $query->with([
                                'related' => function ($query) use ($game) {
                                    $game->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'songs':
                        $includeArray['mediaSongs'] = function ($query) {
                            $query->with([
                                'song' => function ($query) {
                                    $query->with(['media', 'mediaStat', 'translation']);
                                },
                                'model'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'staff':
                        $includeArray['mediaStaff'] = function ($query) {
                            $query->with(['model', 'staffRole', 'person.media'])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'studios':
                        $includeArray['studios'] = function ($query) {
                            $query->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $game->loadMissing($includeArray);

        // Show the game details response
        return JSONResult::success([
            'data' => GameResource::collection([$game])
        ])->withHeaders($this->catalogCacheHeaders($request, $fingerprint));
    }

    /**
     * Returns detailed information of requested IDs.
     *
     * @param GetIndexRequest $request
     *
     * @return JsonResponse
     */
    public function views(GetIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        $game = Game::whereIn('id', $data['ids'] ?? []);
        $game->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
            ->when(auth()->user(), function ($query, $user) use ($game) {
                $game->with(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id],
                    ]);
                }, 'library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }])
                    ->withExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                    ]);
            });

        $includeArray = [];
        if ($includeInput = $request->input('include')) {
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            foreach ($includes as $include) {
                switch ($include) {
                    case 'cast':
                        $includeArray['cast'] = function ($query) {
                            $query->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'characters':
                        $includeArray['characters'] = function ($query) {
                            $query->with(['media', 'translation'])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-shows':
                        $includeArray['animeRelations'] = function ($query) use ($game) {
                            $query->with([
                                'related' => function ($query) use ($game) {
                                    $game->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-literatures':
                        $includeArray['mangaRelations'] = function ($query) use ($game) {
                            $query->with([
                                'related' => function ($query) use ($game) {
                                    $game->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-games':
                        $includeArray['gameRelations'] = function ($query) use ($game) {
                            $query->with([
                                'related' => function ($query) use ($game) {
                                    $game->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'songs':
                        $includeArray['mediaSongs'] = function ($query) {
                            $query->with([
                                'song' => function ($query) {
                                    $query->with(['media', 'mediaStat', 'translation']);
                                },
                                'model'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'staff':
                        $includeArray['mediaStaff'] = function ($query) {
                            $query->with(['model', 'staffRole', 'person.media'])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'studios':
                        $includeArray['studios'] = function ($query) {
                            $query->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $game->with($includeArray);

        // Show the anime details response
        return JSONResult::success([
            'data' => GameResource::collection($game->get()),
        ]);
    }

    /**
     * Returns game season.
     *
     * @param GetBrowseSeasonRequest $request
     *
     * @return JsonResponse
     * @throws InvalidEnumKeyException|BindingResolutionException|ConnectionException
     */
    public function browseSeason(GetBrowseSeasonRequest $request)
    {
        // Override parameters
        $request->merge([
            'kind' => BrowseSeasonKind::Game
        ]);

        // Convert request type
        $app = app();
        $getBrowseSeasonRequest = GetBrowseSeasonRequest::createFrom($request)
            ->setContainer($app) // Necessary or validation fails (validate on null)
            ->setRedirector($app->make(Redirector::class)); // Necessary or validation failure fails (422)
        $getBrowseSeasonRequest->validateResolved(); // Necessary for preparing for validation

        return (new BrowseSeasonController())
            ->view($getBrowseSeasonRequest);
    }

    /**
     * Returns character information of a game.
     *
     * @param GetPaginatedRequest $request
     * @param game                $game
     *
     * @return JsonResponse
     */
    public function characters(GetPaginatedRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $game->characters()
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the cast information of a game.
     *
     * @param GetPaginatedRequest $request
     * @param game                $game
     *
     * @return JsonResponse
     */
    public function cast(GetPaginatedRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the anime cast
        $cast = $game->cast()
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $cast->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => GameCastResourceIdentity::collection($cast),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-shows information of a game.
     *
     * @param GetPaginatedRequest $request
     * @param game                $game
     *
     * @return JsonResponse
     */
    public function relatedShows(GetPaginatedRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the related shows
        $relatedShows = $game->animeRelations()
            ->with([
                'related' => function ($query) use ($game) {
                    $game->viewableViaParent($query)
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['mediaRatings' => function ($query) use ($user) {
                                $query->where([
                                    ['user_id', '=', $user->id]
                                ]);
                            }, 'library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }])
                                ->withExists([
                                    'favoriters as isFavorited' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    },
                                    'reminderers as isReminded' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    },
                                ]);
                        });
                },
                'relation'
            ])
            ->orderBy(MediaRelation::TABLE_NAME . '.id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedShows->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedShows),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-literatures information of a game.
     *
     * @param GetPaginatedRequest $request
     * @param game                $game
     *
     * @return JsonResponse
     */
    public function relatedLiteratures(GetPaginatedRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the related literatures
        $relatedLiterature = $game->mangaRelations()
            ->with([
                'related' => function ($query) use ($game) {
                    $game->viewableViaParent($query)
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['mediaRatings' => function ($query) use ($user) {
                                $query->where([
                                    ['user_id', '=', $user->id]
                                ]);
                            }, 'library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }])
                                ->withExists([
                                    'favoriters as isFavorited' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }
                                ]);
                        });
                },
                'relation'
            ])
            ->orderBy(MediaRelation::TABLE_NAME . '.id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedLiterature->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedLiterature),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-games information of a game.
     *
     * @param GetPaginatedRequest $request
     * @param game                $game
     *
     * @return JsonResponse
     */
    public function relatedGames(GetPaginatedRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the related games
        $relatedGame = $game->gameRelations()
            ->with([
                'related' => function ($query) use ($game) {
                    $game->viewableViaParent($query)
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['mediaRatings' => function ($query) use ($user) {
                                $query->where([
                                    ['user_id', '=', $user->id]
                                ]);
                            }, 'library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }])
                                ->withExists([
                                    'favoriters as isFavorited' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }
                                ]);
                        });
                },
                'relation'
            ])
            ->orderBy(MediaRelation::TABLE_NAME . '.id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedGame->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedGame),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns song information for a game
     *
     * @param GetMediaSongsRequest $request
     * @param game                 $game
     *
     * @return JsonResponse
     */
    public function songs(GetMediaSongsRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the seasons
        $limit = ($data['limit'] ?? -1) == -1 ? 150 : $data['limit'];
        $mediaSongs = $game->mediaSongs()
            ->with([
                'song' => function ($query) {
                    $query->with([
                        'media',
                        'mediaStat',
                        'translation'
                    ]);
                },
            ])
            ->cursorPaginate($limit);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaSongs->nextPageUrl() ?? '');

        // Set model relation
        $mediaSongs->each(function ($song) use ($game) {
            $song->setRelation('model', $game);
        });

        return JSONResult::success([
            'data' => MediaSongResource::collection($mediaSongs),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns staff information of a game.
     *
     * @param GetPaginatedRequest $request
     * @param game                $game
     *
     * @return JsonResponse
     */
    public function staff(GetPaginatedRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the staff
        $staff = $game->mediaStaff()
            ->with([
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'staffRole'
            ])
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $staff->nextPageUrl() ?? '');

        // Set model relation
        $staff->each(function ($song) use ($game) {
            $song->setRelation('model', $game);
        });

        return JSONResult::success([
            'data' => MediaStaffResource::collection($staff),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the studios information of a game.
     *
     * @param GetPaginatedRequest $request
     * @param game                $game
     *
     * @return JsonResponse
     */
    public function studios(GetPaginatedRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the anime studios
        $mediaStudios = $game->studios()
            ->with([
                'media',
                'mediaStat',
                'successor',
                'predecessors',
                'tvRating',
            ])
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaStudios->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => StudioResource::collection($mediaStudios),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the more anime made by the same studio.
     *
     * @param GetPaginatedRequest $request
     * @param game                $game
     *
     * @return JsonResponse
     */
    public function moreByStudio(GetPaginatedRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();
        $studioGames = new CursorPaginator(collect(), $data['limit'] ?? 25);

        // Get the anime studios
        if ($mediaStudio = $game->studios()->firstWhere('is_studio', '=', true)) {
            $studioGames = $mediaStudio->games()
                ->where('model_id', '!=', $game->id)
                ->cursorPaginate($data['limit'] ?? 25);
        } else if ($mediaStudio = $game->studios()->first()) {
            $studioGames = $mediaStudio->games()
                ->where('model_id', '!=', $game->id)
                ->cursorPaginate($data['limit'] ?? 25);
        }

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $studioGames->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => GameResourceIdentity::collection($studioGames),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for a game item
     *
     * @param RateModelRequest $request
     * @param game             $game
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rate(RateModelRequest $request, game $game): JsonResponse
    {
        $user = auth()->user();

        // Check if the user is already tracking the anime
        if ($user->hasNotTracked($game)) {
            throw new AuthorizationException(__('Please add ":x" to your library first.', ['x' => $game->title]));
        }

        $data = $request->validated();
        $user->rateMediaModel($game, $data['rating'], $data['description'] ?? null);

        return JSONResult::success();
    }

    /**
     * Delete the user's media rating associated with the given model.
     *
     * @param Game $game
     *
     * @return JsonResponse
     */
    public function deleteRating(Game $game)
    {
        auth()->user()->mediaRatings()
            ->where([
                ['model_id', '=', $game->id],
                ['model_type', '=', $game->getMorphClass()],
            ])
            ->forceDelete();

        return JSONResult::success();
    }

    /**
     * Retrieves upcoming games results
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    public function upcoming(GetPaginatedRequest $request): JsonResponse
    {
        $data = $request->validated();

        $game = Game::upcoming(-1)
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $game->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => GameResourceIdentity::collection($game),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the reviews of a Game.
     *
     * @param GetPaginatedRequest $request
     * @param Game                $game
     *
     * @return JsonResponse
     */
    public function reviews(GetPaginatedRequest $request, Game $game): JsonResponse
    {
        $data = $request->validated();

        $reviews = $game->mediaRatings()
            ->withoutTvRatings()
            ->with([
                'user' => function ($query) {
                    $query->withProfileEagerLoad(auth()->user());
                }
            ])
            ->where('description', '!=', null)
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $reviews->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRatingResource::collection($reviews),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
