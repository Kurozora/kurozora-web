<?php

namespace App\Http\Controllers\API\v1;

use App\Events\AnimeViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetAnimeCharactersRequest;
use App\Http\Requests\GetAnimeMoreByStudioRequest;
use App\Http\Requests\GetAnimeReviewsRequest;
use App\Http\Requests\GetAnimeSeasonsRequest;
use App\Http\Requests\GetAnimeStudiosRequest;
use App\Http\Requests\GetMediaCastRequest;
use App\Http\Requests\GetMediaRelatedGamesRequest;
use App\Http\Requests\GetMediaRelatedLiteraturesRequest;
use App\Http\Requests\GetMediaRelatedShowsRequest;
use App\Http\Requests\GetMediaSongsRequest;
use App\Http\Requests\GetMediaStaffRequest;
use App\Http\Requests\GetUpcomingAnimeRequest;
use App\Http\Requests\RateAnimeRequest;
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
use App\Models\MediaRating;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AnimeController extends Controller
{
    /**
     * Returns detailed information of an Anime.
     *
     * @param Request $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function view(Request $request, Anime $anime): JsonResponse
    {
        // Call the AnimeViewed event
        AnimeViewed::dispatch($anime);

        $anime->load(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) use ($anime) {
                $anime->load(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id]
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
                            $query->with(['media', 'translations'])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-shows':
                        $includeArray['animeRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating']);
                                },
                                'relation'
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-literatures':
                        $includeArray['mangaRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating']);
                                },
                                'relation'
                            ])
                                ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-games':
                        $includeArray['gameRelations'] = function ($query) {
                            $query->with([
                                'related' => function ($query) {
                                    $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating']);
                                },
                                'relation'
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
                            $query->with(['song.media', 'song.mediaStat', 'model'])
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
            'data' => AnimeResource::collection([$anime])
        ]);
    }

    /**
     * Returns character information of an Anime.
     *
     * @param GetAnimeCharactersRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function characters(GetAnimeCharactersRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $anime->characters()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl());

        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the cast information of an Anime.
     *
     * @param GetMediaCastRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function cast(GetMediaCastRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the anime cast
        $animeCast = $anime->cast()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $animeCast->nextPageUrl());

        return JSONResult::success([
            'data' => ShowCastResourceIdentity::collection($animeCast),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-shows information of an Anime.
     *
     * @param GetMediaRelatedShowsRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function relatedShows(GetMediaRelatedShowsRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the related shows
        $relatedShows = $anime->animeRelations()
            ->with([
                'related' => function ($query) {
                    $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
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
     * Returns related-literatures information of an Anime.
     *
     * @param GetMediaRelatedLiteraturesRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function relatedLiteratures(GetMediaRelatedLiteraturesRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the related literatures
        $relatedLiterature = $anime->mangaRelations()
            ->with([
                'related' => function ($query) {
                    $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
                        ->when(auth()->useR(), function ($query, $user) {
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
     * Returns related-literatures information of an Anime.
     *
     * @param GetMediaRelatedGamesRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function relatedGames(GetMediaRelatedGamesRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the related literatures
        $relatedGame = $anime->gameRelations()
            ->with([
                'related' => function ($query) {
                    $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
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
     * Returns season information for an Anime
     *
     * @param GetAnimeSeasonsRequest $request
     * @param Anime $anime
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
        $nextPageURL = str_replace($request->root(), '', $seasons->nextPageUrl());

        return JSONResult::success([
            'data' => SeasonResourceIdentity::collection($seasons),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns song information for an Anime
     *
     * @param GetMediaSongsRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function songs(GetMediaSongsRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the media songs
        $limit = ($data['limit'] ?? -1) == -1 ? 150 : $data['limit'];
        $mediaSongs = $anime->mediaSongs()
            ->with(['song.media', 'song.mediaStat', 'model'])
            ->paginate($limit, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaSongs->nextPageUrl());

        return JSONResult::success([
            'data' => MediaSongResource::collection($mediaSongs),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns staff information of an Anime.
     *
     * @param GetMediaStaffRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function staff(GetMediaStaffRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the staff
        $staff = $anime->mediaStaff()
            ->with([
                'model',
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'staff_role'
            ])
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $staff->nextPageUrl());

        return JSONResult::success([
            'data' => MediaStaffResource::collection($staff),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the studios information of an Anime.
     *
     * @param GetAnimeStudiosRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function studios(GetAnimeStudiosRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the anime studios
        $mediaStudios = $anime->studios()
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
     * @param GetAnimeMoreByStudioRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function moreByStudio(GetAnimeMoreByStudioRequest $request, Anime $anime): JsonResponse
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
        $nextPageURL = str_replace($request->root(), '', $studioAnimes->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($studioAnimes),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for an Anime item
     *
     * @param RateAnimeRequest $request
     * @param Anime $anime
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rateAnime(RateAnimeRequest $request, Anime $anime): JsonResponse
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

        // Try to modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->animeRatings()
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
                    'rating'        => $givenRating,
                    'description'   => $description ?? $foundRating->description,
                ]);
            }
        } else {
            // Only insert the rating if it's rated higher than 0
            if ($givenRating > 0) {
                MediaRating::create([
                    'user_id'       => $user->id,
                    'model_id'      => $anime->id,
                    'model_type'    => $anime->getMorphClass(),
                    'rating'        => $givenRating,
                    'description'   => $description
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Retrieves upcoming Anime results
     *
     * @param GetUpcomingAnimeRequest $request
     * @return JsonResponse
     */
    public function upcoming(GetUpcomingAnimeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $anime = Anime::upcoming(-1)
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the reviews of an Anime.
     *
     * @param GetAnimeReviewsRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function reviews(GetAnimeReviewsRequest $request, Anime $anime): JsonResponse
    {
        $reviews = $anime->mediaRatings()
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
                        ->withCount(['followers', 'following', 'mediaRatings']);
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
