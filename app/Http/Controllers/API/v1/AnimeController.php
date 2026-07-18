<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\BrowseSeasonKind;
use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Events\ModelViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetAnimeSeasonsRequest;
use App\Http\Requests\GetBrowseSeasonRequest;
use App\Http\Requests\GetIndexRequest;
use App\Http\Requests\GetMediaSongsRequest;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\RateModelRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\AnimeMappingResource;
use App\Http\Resources\AnimeResource;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\MediaRelatedResource;
use App\Http\Resources\MediaSongResource;
use App\Http\Resources\MediaStaffResource;
use App\Http\Resources\SeasonResourceIdentity;
use App\Http\Resources\ShowCastResourceIdentity;
use App\Http\Resources\StudioResource;
use App\Models\Anime;
use App\Models\MediaRelation;
use App\Support\UserLibraryTouch;
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
use Illuminate\Support\Facades\Cache;

class AnimeController extends Controller
{
    use WithCatalogCacheHeaders;

    /**
     * Returns every anime's slug and the external service identifiers it maps to.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function mapping(Request $request): JsonResponse
    {
        $fingerprint = [
            'count' => Anime::count(),
            'updatedAt' => (string) Anime::max('updated_at'),
        ];
        $etag = $this->catalogETag($fingerprint);
        $cacheHeaders = [
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=3600, stale-while-revalidate=300',
        ];

        if ($request->header('If-None-Match') === $etag) {
            return response()->json(null, JsonResponse::HTTP_NOT_MODIFIED, $cacheHeaders);
        }

        $mapping = Cache::remember('anime.mapping:' . $etag, now()->addDay(), function () {
            return AnimeMappingResource::collection(
                Anime::select([
                    'slug',
                    'mal_id',
                    'anilist_id',
                    'kitsu_id',
                    'anidb_id',
                    'animeplanet_id',
                    'anisearch_id',
                    'livechart_id',
                    'notify_id',
                    'syoboi_id',
                    'trakt_id',
                    'tvdb_id',
                    'imdb_id',
                ])->get()
            )->resolve();
        });

        return JSONResult::success(['data' => $mapping])
            ->withHeaders($cacheHeaders);
    }

    /**
     * Returns the anime index.
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
                    SearchType::Shows,
                ],
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
     * Returns detailed information of an Anime.
     *
     * @param Request    $request
     * @param Anime|null $anime
     *
     * @return JsonResponse
     */
    public function view(Request $request, ?Anime $anime): JsonResponse
    {
        $includeInput = $request->input('include');
        $includes = is_string($includeInput) ? explode(',', $includeInput) : (is_array($includeInput) ? $includeInput : []);
        sort($includes);

        $fingerprint = [
            'kind' => 'anime',
            'publicId' => $anime->public_id,
            'updatedAt' => optional($anime->updated_at)->toIso8601String(),
            'locale' => app()->getLocale(),
            'tvRating' => (int) $request->attributes->get('tvRating', 4),
            'include' => $includes,
        ];

        $notModified = $this->returnIfNotModifiedCatalog($request, $fingerprint);
        if ($notModified !== null) {
            return $notModified;
        }

        // Call the ModelViewed event
        ModelViewed::dispatch($anime, $request->ip());

        $anime->load(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
            ->when(auth()->user(), function ($query, $user) use ($anime) {
                $anime->load(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id],
                    ]);
                }, 'library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }])
                    ->loadExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                        'reminderers as isReminded' => function ($query) use ($user) {
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
                            $query->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'characters':
                        $includeArray['characters'] = function ($query) {
                            $query->with(['media', 'translation'])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-shows':
                        $includeArray['animeRelations'] = function ($query) use ($anime) {
                            $query->with([
                                'related' => function ($query) use ($anime) {
                                    $anime->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-literatures':
                        $includeArray['mangaRelations'] = function ($query) use ($anime) {
                            $query->with([
                                'related' => function ($query) use ($anime) {
                                    $anime->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-games':
                        $includeArray['gameRelations'] = function ($query) use ($anime) {
                            $query->with([
                                'related' => function ($query) use ($anime) {
                                    $anime->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'seasons':
                        $includeArray['seasons'] = function ($query) {
                            $query->orderBy('number', 'desc')
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'songs':
                        $includeArray['mediaSongs'] = function ($query) {
                            $query->with([
                                'song' => function ($query) {
                                    $query->with(['media', 'mediaStat', 'translation']);
                                },
                                'model',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'staff':
                        $includeArray['mediaStaff'] = function ($query) {
                            $query->with(['model', 'staffRole', 'person.media'])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'studios':
                        $includeArray['studios'] = function ($query) {
                            $query->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $anime->loadMissing($includeArray);

        // Show the Anime details response
        return JSONResult::success([
            'data' => AnimeResource::collection([$anime]),
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

        $anime = Anime::whereIn('id', $data['ids'] ?? []);
        $anime->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
            ->when(auth()->user(), function ($query, $user) use ($anime) {
                $anime->with(['mediaRatings' => function ($query) use ($user) {
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
                        'reminderers as isReminded' => function ($query) use ($user) {
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
                            $query->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'characters':
                        $includeArray['characters'] = function ($query) {
                            $query->with(['media', 'translation'])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-shows':
                        $includeArray['animeRelations'] = function ($query) use ($anime) {
                            $query->with([
                                'related' => function ($query) use ($anime) {
                                    $anime->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-literatures':
                        $includeArray['mangaRelations'] = function ($query) use ($anime) {
                            $query->with([
                                'related' => function ($query) use ($anime) {
                                    $anime->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-games':
                        $includeArray['gameRelations'] = function ($query) use ($anime) {
                            $query->with([
                                'related' => function ($query) use ($anime) {
                                    $anime->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'seasons':
                        $includeArray['seasons'] = function ($query) {
                            $query->orderBy('number', 'desc')
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'songs':
                        $includeArray['mediaSongs'] = function ($query) {
                            $query->with([
                                'song' => function ($query) {
                                    $query->with(['media', 'mediaStat', 'translation']);
                                },
                                'model',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'staff':
                        $includeArray['mediaStaff'] = function ($query) {
                            $query->with(['model', 'staffRole', 'person.media'])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'studios':
                        $includeArray['studios'] = function ($query) {
                            $query->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $anime->with($includeArray);

        // Show the anime details response
        return JSONResult::success([
            'data' => AnimeResource::collection($anime->get()),
        ]);
    }

    /**
     * Returns anime season.
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
            'kind' => BrowseSeasonKind::Anime
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
     * Returns character information of an Anime.
     *
     * @param GetPaginatedRequest $request
     * @param Anime               $anime
     *
     * @return JsonResponse
     */
    public function characters(GetPaginatedRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $anime->characters()
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns the cast information of an Anime.
     *
     * @param GetPaginatedRequest $request
     * @param Anime               $anime
     *
     * @return JsonResponse
     */
    public function cast(GetPaginatedRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the anime cast
        $animeCast = $anime->cast()
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $animeCast->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => ShowCastResourceIdentity::collection($animeCast),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns related-shows information of an Anime.
     *
     * @param GetPaginatedRequest $request
     * @param Anime               $anime
     *
     * @return JsonResponse
     */
    public function relatedShows(GetPaginatedRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the related shows
        $relatedShows = $anime->animeRelations()
            ->with([
                'related' => function ($query) use ($anime) {
                    $anime->viewableViaParent($query)
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['mediaRatings' => function ($query) use ($user) {
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
                                    'reminderers as isReminded' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    },
                                ]);
                        });
                },
                'relation',
            ])
            ->orderBy(MediaRelation::TABLE_NAME . '.id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedShows->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedShows),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns related-literatures information of an Anime.
     *
     * @param GetPaginatedRequest $request
     * @param Anime               $anime
     *
     * @return JsonResponse
     */
    public function relatedLiteratures(GetPaginatedRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the related literatures
        $relatedLiterature = $anime->mangaRelations()
            ->with([
                'related' => function ($query) use ($anime) {
                    $anime->viewableViaParent($query)
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['mediaRatings' => function ($query) use ($user) {
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
                },
                'relation',
            ])
            ->orderBy(MediaRelation::TABLE_NAME . '.id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedLiterature->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedLiterature),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns related-literatures information of an Anime.
     *
     * @param GetPaginatedRequest $request
     * @param Anime               $anime
     *
     * @return JsonResponse
     */
    public function relatedGames(GetPaginatedRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the related literatures
        $relatedGame = $anime->gameRelations()
            ->with([
                'related' => function ($query) use ($anime) {
                    $anime->viewableViaParent($query)
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['mediaRatings' => function ($query) use ($user) {
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
                },
                'relation',
            ])
            ->orderBy(MediaRelation::TABLE_NAME . '.id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedGame->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedGame),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns season information for an Anime
     *
     * @param GetAnimeSeasonsRequest $request
     * @param Anime                  $anime
     *
     * @return JsonResponse
     */
    public function seasons(GetAnimeSeasonsRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();
        $reversed = $data['reversed'] ?? false;

        // Get the seasons
        $seasons = $anime->seasons()
            ->orderBy('number', $reversed ? 'desc' : 'asc')
            ->orderBy('id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $seasons->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => SeasonResourceIdentity::collection($seasons),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns song information for an Anime
     *
     * @param GetMediaSongsRequest $request
     * @param Anime                $anime
     *
     * @return JsonResponse
     */
    public function songs(GetMediaSongsRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the media songs
        $limit = ($data['limit'] ?? -1) == -1 ? 150 : $data['limit'];
        $mediaSongs = $anime->mediaSongs()
            ->with([
                'song' => function ($query) {
                    $query->with([
                        'media',
                        'mediaRatings',
                        'mediaStat',
                        'translation',
                    ]);
                },
            ])
            ->cursorPaginate($limit);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaSongs->nextPageUrl() ?? '');

        // Set model relation
        $mediaSongs->each(function ($song) use ($anime) {
            $song->setRelation('model', $anime);
        });

        return JSONResult::success([
            'data' => MediaSongResource::collection($mediaSongs),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns staff information of an Anime.
     *
     * @param GetPaginatedRequest $request
     * @param Anime               $anime
     *
     * @return JsonResponse
     */
    public function staff(GetPaginatedRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the staff
        $staff = $anime->mediaStaff()
            ->with([
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'staffRole',
            ])
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $staff->nextPageUrl() ?? '');

        // Set model relation
        $staff->each(function ($song) use ($anime) {
            $song->setRelation('model', $anime);
        });

        return JSONResult::success([
            'data' => MediaStaffResource::collection($staff),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns the studios information of an Anime.
     *
     * @param GetPaginatedRequest $request
     * @param Anime               $anime
     *
     * @return JsonResponse
     */
    public function studios(GetPaginatedRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the anime studios
        $mediaStudios = $anime->studios()
            ->with([
                'media',
                'mediaRatings',
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
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns the more anime made by the same studio.
     *
     * @param GetPaginatedRequest $request
     * @param Anime               $anime
     *
     * @return JsonResponse
     */
    public function moreByStudio(GetPaginatedRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();
        $studioAnimes = new CursorPaginator(collect(), $data['limit'] ?? 25);

        // Get the anime studios
        if ($mediaStudio = $anime->studios()->firstWhere('is_studio', '=', true)) {
            $studioAnimes = $mediaStudio->anime()
                ->where('model_id', '!=', $anime->id)
                ->cursorPaginate($data['limit'] ?? 25);
        } else if ($mediaStudio = $anime->studios()->first()) {
            $studioAnimes = $mediaStudio->anime()
                ->where('model_id', '!=', $anime->id)
                ->cursorPaginate($data['limit'] ?? 25);
        }

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $studioAnimes->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($studioAnimes),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Adds a rating for an Anime item
     *
     * @param RateModelRequest $request
     * @param Anime            $anime
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rate(RateModelRequest $request, Anime $anime): JsonResponse
    {
        $user = auth()->user();

        // Check if the user is already tracking the anime
        if ($user->hasNotTracked($anime)) {
            throw new AuthorizationException(__('Please add ":x" to your library first.', ['x' => $anime->title]));
        }

        $data = $request->validated();
        $user->rateMediaModel($anime, $data['rating'], $data['description'] ?? null);

        return JSONResult::success();
    }

    /**
     * Delete the user's media rating associated with the given model.
     *
     * @param Anime $anime
     *
     * @return JsonResponse
     */
    public function deleteRating(Anime $anime)
    {
        auth()->user()->mediaRatings()
            ->where([
                ['model_id', '=', $anime->id],
                ['model_type', '=', $anime->getMorphClass()],
            ])
            ->forceDelete();

        return JSONResult::success();
    }

    /**
     * Retrieves upcoming Anime results
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    public function upcoming(GetPaginatedRequest $request): JsonResponse
    {
        $data = $request->validated();

        $anime = Anime::upcoming(-1)
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns the reviews of an Anime.
     *
     * @param GetPaginatedRequest $request
     * @param Anime               $anime
     *
     * @return JsonResponse
     */
    public function reviews(GetPaginatedRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        $reviews = $anime->mediaRatings()
            ->withoutTvRatings()
            ->with([
                'user' => function ($query) {
                    $query->withProfileEagerLoad(auth()->user());
                },
            ])
            ->where('description', '!=', null)
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $reviews->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRatingResource::collection($reviews),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }
}
