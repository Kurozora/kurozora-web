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
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Scopes\TvRatingScope;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Redirector;

class AnimeController extends Controller
{
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
        // Call the ModelViewed event
        ModelViewed::dispatch($anime, $request->ip());

        $anime->load(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
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
                        $includeArray['animeRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->withoutGlobalScopes([TvRatingScope::class])
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin']);
                                },
                                'relation',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-literatures':
                        $includeArray['mangaRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->withoutGlobalScopes([TvRatingScope::class])
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin']);
                                },
                                'relation',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-games':
                        $includeArray['gameRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->withoutGlobalScopes([TvRatingScope::class])
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin']);
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
                            $query->with(['model', 'staff_role', 'person.media'])
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
        ]);
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
        $anime->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
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
                        $includeArray['animeRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->withoutGlobalScopes([TvRatingScope::class])
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin']);
                                },
                                'relation',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-literatures':
                        $includeArray['mangaRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->withoutGlobalScopes([TvRatingScope::class])
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin']);
                                },
                                'relation',
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-games':
                        $includeArray['gameRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->withoutGlobalScopes([TvRatingScope::class])
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin']);
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
                            $query->with(['model', 'staff_role', 'person.media'])
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
     * @param int                    $year
     * @param string                 $season
     *
     * @return JsonResponse
     * @throws InvalidEnumKeyException|BindingResolutionException|ConnectionException
     */
    public function browseSeason(GetBrowseSeasonRequest $request, int $year, string $season)
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
            ->view($getBrowseSeasonRequest, $year, $season);
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
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

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
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

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
                'related' => function ($query) {
                    $query->withoutGlobalScopes([TvRatingScope::class])
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
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
            ->orderBy(Anime::TABLE_NAME . '.id')
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
                'related' => function ($query) {
                    $query->withoutGlobalScopes([TvRatingScope::class])
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
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
            ->orderBy(Manga::TABLE_NAME . '.id')
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
                'related' => function ($query) {
                    $query->withoutGlobalScopes([TvRatingScope::class])
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
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
            ->orderBy(Game::TABLE_NAME . '.id')
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
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

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
                        'mediaStat',
                        'translation',
                    ]);
                },
            ])
            ->paginate($limit, page: $data['page'] ?? 1);

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
                'staff_role',
            ])
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

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
                'mediaStat',
                'successor',
                'predecessors',
                'tv_rating',
            ])
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

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
        $studioAnimes = new LengthAwarePaginator([], 0, 1);

        // Get the anime studios
        if ($mediaStudio = $anime->studios()->firstWhere('is_studio', '=', true)) {
            $studioAnimes = $mediaStudio->anime()
                ->where('model_id', '!=', $anime->id)
                ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);
        } else if ($mediaStudio = $anime->studios()->first()) {
            $studioAnimes = $mediaStudio->anime()
                ->where('model_id', '!=', $anime->id)
                ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);
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
    public function rateAnime(RateModelRequest $request, Anime $anime): JsonResponse
    {
        $user = auth()->user();

        // Check if the user is already tracking the anime
        if ($user->hasNotTracked($anime)) {
            throw new AuthorizationException(__('Please add ":x" to your library first.', ['x' => $anime->title]));
        }

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];
        $description = $data['description'] ?? null;

        // Modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->animeRatings()
            ->withoutTvRatings()
            ->where('model_id', '=', $anime->id)
            ->first();

        // The rating exists
        if ($foundRating) {
            // If the given rating is 0
            if ($givenRating <= 0) {
                // Delete the rating
                $foundRating->delete();
            } else {
                // Update the current rating
                $foundRating->update([
                    'rating' => $givenRating,
                    'description' => $description ?? $foundRating->description,
                ]);
            }
        } else {
            // Only insert the rating if it's rated higher than 0
            if ($givenRating > 0) {
                MediaRating::create([
                    'user_id' => $user->id,
                    'model_id' => $anime->id,
                    'model_type' => $anime->getMorphClass(),
                    'rating' => $givenRating,
                    'description' => $description,
                ]);
            }
        }

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
        $reviews = $anime->mediaRatings()
            ->withoutTvRatings()
            ->with([
                'user' => function ($query) {
                    $query->with([
                        'badges' => function ($query) {
                            $query->with(['media']);
                        },
                        'media',
                        'tokens' => function ($query) {
                            $query
                                ->orderBy('last_used_at', 'desc')
                                ->limit(1);
                        },
                        'sessions' => function ($query) {
                            $query
                                ->orderBy('last_activity', 'desc')
                                ->limit(1);
                        },
                    ])
                        ->withCount(['followers', 'followedModels as following_count', 'mediaRatings']);
                },
            ])
            ->where('description', '!=', null)
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $reviews->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRatingResource::collection($reviews),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }
}
