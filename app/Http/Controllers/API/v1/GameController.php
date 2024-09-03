<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Events\ModelViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetGameCharactersRequest;
use App\Http\Requests\GetGameMoreByStudioRequest;
use App\Http\Requests\GetGameReviewsRequest;
use App\Http\Requests\GetGameStudiosRequest;
use App\Http\Requests\GetMediaCastRequest;
use App\Http\Requests\GetMediaRelatedGamesRequest;
use App\Http\Requests\GetMediaRelatedLiteraturesRequest;
use App\Http\Requests\GetMediaRelatedShowsRequest;
use App\Http\Requests\GetMediaSongsRequest;
use App\Http\Requests\GetMediaStaffRequest;
use App\Http\Requests\GetUpcomingGameRequest;
use App\Http\Requests\RateGameRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\GameResource;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\MediaRelatedResource;
use App\Http\Resources\MediaSongResource;
use App\Http\Resources\MediaStaffResource;
use App\Http\Resources\ShowCastResourceIdentity;
use App\Http\Resources\StudioResource;
use App\Models\Game;
use App\Models\MediaRating;
use App\Scopes\TvRatingScope;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Redirector;

class GameController extends Controller
{
    /**
     * Returns the games index.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws BindingResolutionException
     */
    public function index(Request $request): JsonResponse
    {
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
        // Call the ModelViewed event
        ModelViewed::dispatch($game, $request->ip());

        $user = auth()->user();

        $game->load(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
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
                            $query->with(['media', 'translations'])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-shows':
                        $includeArray['animeRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->withoutGlobalScopes([TvRatingScope::class])
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating']);
                                },
                                'relation'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-literatures':
                        $includeArray['mangaRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->withoutGlobalScopes([TvRatingScope::class])
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating']);
                                },
                                'relation'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-games':
                        $includeArray['gameRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->withoutGlobalScopes([TvRatingScope::class])
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating']);
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
                                    $query->with(['media', 'mediaStat', 'translations']);
                                },
                                'model'
                            ])
                                ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'staff':
                        $includeArray['mediaStaff'] = function ($query) {
                            $query->with(['model', 'staff_role', 'person.media'])
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
        ]);
    }

    /**
     * Returns character information of a game.
     *
     * @param GetGameCharactersRequest $request
     * @param game                     $game
     *
     * @return JsonResponse
     */
    public function characters(GetGameCharactersRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $game->characters()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl());

        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the cast information of a game.
     *
     * @param GetMediaCastRequest $request
     * @param game                $game
     *
     * @return JsonResponse
     */
    public function cast(GetMediaCastRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the anime cast
        $game = $game->cast()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $game->nextPageUrl());

        return JSONResult::success([
            'data' => ShowCastResourceIdentity::collection($game),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-shows information of a game.
     *
     * @param GetMediaRelatedShowsRequest $request
     * @param game                        $game
     *
     * @return JsonResponse
     */
    public function relatedShows(GetMediaRelatedShowsRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the related shows
        $relatedShows = $game->animeRelations()
            ->with([
                'related' => function ($query) {
                    $query->withoutGlobalScopes([TvRatingScope::class])
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
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
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedShows->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedShows),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-literatures information of a game.
     *
     * @param GetMediaRelatedLiteraturesRequest $request
     * @param game                              $game
     *
     * @return JsonResponse
     */
    public function relatedLiteratures(GetMediaRelatedLiteraturesRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the related literatures
        $relatedLiterature = $game->mangaRelations()
            ->with([
                'related' => function ($query) {
                    $query->withoutGlobalScopes([TvRatingScope::class])
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
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
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedLiterature->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedLiterature),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-games information of a game.
     *
     * @param GetMediaRelatedGamesRequest $request
     * @param game                        $game
     *
     * @return JsonResponse
     */
    public function relatedGames(GetMediaRelatedGamesRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the related games
        $relatedGame = $game->gameRelations()
            ->with([
                'related' => function ($query) {
                    $query->withoutGlobalScopes([TvRatingScope::class])
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
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
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedGame->nextPageUrl());

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
                        'translations'
                    ]);
                },
            ])
            ->paginate($limit, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaSongs->nextPageUrl());

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
     * @param GetMediaStaffRequest $request
     * @param game                 $game
     *
     * @return JsonResponse
     */
    public function staff(GetMediaStaffRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the staff
        $staff = $game->mediaStaff()
            ->with([
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'staff_role'
            ])
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $staff->nextPageUrl());

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
     * @param GetGameStudiosRequest $request
     * @param game                  $game
     *
     * @return JsonResponse
     */
    public function studios(GetGameStudiosRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the anime studios
        $mediaStudios = $game->studios()
            ->with(['media'])
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaStudios->nextPageUrl());

        return JSONResult::success([
            'data' => StudioResource::collection($mediaStudios),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the more anime made by the same studio.
     *
     * @param GetGameMoreByStudioRequest $request
     * @param game                       $game
     *
     * @return JsonResponse
     */
    public function moreByStudio(GetGameMoreByStudioRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();
        $studioGames = new LengthAwarePaginator([], 0, 1);

        // Get the anime studios
        if ($mediaStudio = $game->studios()->firstWhere('is_studio', '=', true)) {
            $studioGames = $mediaStudio->games()
                ->where('model_id', '!=', $game->id)
                ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);;
        } else if ($mediaStudio = $game->studios()->first()) {
            $studioGames = $mediaStudio->games()
                ->where('model_id', '!=', $game->id)
                ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);
        }

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $studioGames->nextPageUrl());

        return JSONResult::success([
            'data' => GameResourceIdentity::collection($studioGames),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for an game item
     *
     * @param RateGameRequest $request
     * @param game            $game
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rate(RateGameRequest $request, game $game): JsonResponse
    {
        $user = auth()->user();

        // Check if the user is already tracking the anime
        if ($user->hasNotTracked($game)) {
            throw new AuthorizationException(__('Please add ":x" to your library first.', ['x' => $game->title]));
        }

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];
        $description = $data['description'] ?? null;

        // Modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->gameRatings()
            ->withoutTvRatings()
            ->where('model_id', '=', $game->id)
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
                    'description' => $description
                ]);
            }
        } else {
            // Only insert the rating if it's rated higher than 0
            if ($givenRating > 0) {
                MediaRating::create([
                    'user_id' => $user->id,
                    'model_id' => $game->id,
                    'model_type' => $game->getMorphClass(),
                    'rating' => $givenRating,
                    'description' => $description
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Retrieves upcoming games results
     *
     * @param GetUpcomingGameRequest $request
     *
     * @return JsonResponse
     */
    public function upcoming(GetUpcomingGameRequest $request): JsonResponse
    {
        $data = $request->validated();

        $game = Game::upcoming(-1)
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $game->nextPageUrl());

        return JSONResult::success([
            'data' => GameResourceIdentity::collection($game),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the reviews of a Game.
     *
     * @param GetGameReviewsRequest $request
     * @param Game                  $game
     *
     * @return JsonResponse
     */
    public function reviews(GetGameReviewsRequest $request, Game $game): JsonResponse
    {
        $reviews = $game->mediaRatings()
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
                }
            ])
            ->where('description', '!=', null)
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $reviews->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRatingResource::collection($reviews),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
