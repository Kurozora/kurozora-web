<?php

namespace App\Http\Controllers\API\v1;

use App\Events\MangaViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetMangaCastRequest;
use App\Http\Requests\GetMangaCharactersRequest;
use App\Http\Requests\GetMangaMoreByStudioRequest;
use App\Http\Requests\GetMangaReviewsRequest;
use App\Http\Requests\GetMangaStudiosRequest;
use App\Http\Requests\GetMediaRelatedGamesRequest;
use App\Http\Requests\GetMediaRelatedLiteraturesRequest;
use App\Http\Requests\GetMediaRelatedShowsRequest;
use App\Http\Requests\GetMediaStaffRequest;
use App\Http\Requests\GetUpcomingMangaRequest;
use App\Http\Requests\RateMangaRequest;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\LiteratureResource;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\MangaCastResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\MediaRelatedResource;
use App\Http\Resources\MediaStaffResource;
use App\Http\Resources\StudioResource;
use App\Models\Manga;
use App\Models\MediaRating;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MangaController extends Controller
{
    /**
     * Returns detailed information of a Manga.
     *
     * @param Request $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function view(Request $request, Manga $manga): JsonResponse
    {
        // Call the MangaViewed event
        MangaViewed::dispatch($manga);

        $manga->load(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) use ($manga) {
                $manga->load(['mediaRatings' => function ($query) use ($user) {
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
                            $query->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'characters':
                        $includeArray['characters'] = function ($query) {
                            $query->with(['media', 'translations'])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
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
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
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
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
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
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'staff':
                        $includeArray['mediaStaff'] = function ($query) {
                            $query->with(['model', 'staff_role', 'person.media'])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'studios':
                        $includeArray['studios'] = function ($query) {
                            $query->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $manga->loadMissing($includeArray);

        // Show the Manga details response
        return JSONResult::success([
            'data' => LiteratureResource::collection([$manga])
        ]);
    }

    /**
     * Returns character information of a Manga.
     *
     * @param GetMangaCharactersRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function characters(GetMangaCharactersRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $manga->characters()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl());

        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the cast information of a Manga.
     *
     * @param GetMangaCastRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function cast(GetMangaCastRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the anime cast
        $mangaCast = $manga->cast()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mangaCast->nextPageUrl());

        return JSONResult::success([
            'data' => MangaCastResourceIdentity::collection($mangaCast),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-shows information of a Manga.
     *
     * @param GetMediaRelatedShowsRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function relatedShows(GetMediaRelatedShowsRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the related shows
        $relatedShows = $manga->animeRelations()
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
     * Returns related-mangas information of a Manga.
     *
     * @param GetMediaRelatedLiteraturesRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function relatedLiteratures(GetMediaRelatedLiteraturesRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the related mangas
        $relatedLiteratures = $manga->mangaRelations()
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
        $nextPageURL = str_replace($request->root(), '', $relatedLiteratures->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedLiteratures),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-mangas information of a Manga.
     *
     * @param GetMediaRelatedGamesRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function relatedGames(GetMediaRelatedGamesRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the related mangas
        $relatedGames = $manga->gameRelations()
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
        $nextPageURL = str_replace($request->root(), '', $relatedGames->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedGames),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns staff information of a Manga.
     *
     * @param GetMediaStaffRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function staff(GetMediaStaffRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the staff
        $staff = $manga->mediaStaff()
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
     * Returns the studios information of a Manga.
     *
     * @param GetMangaStudiosRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function studios(GetMangaStudiosRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the manga studios
        $mangaStudios = $manga->studios()
            ->with(['media'])
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mangaStudios->nextPageUrl());

        return JSONResult::success([
            'data' => StudioResource::collection($mangaStudios),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the more manga made by the same studio.
     *
     * @param GetMangaMoreByStudioRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function moreByStudio(GetMangaMoreByStudioRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();
        $studioMangas = new LengthAwarePaginator([], 0, 1);

        // Get the manga studios
        if ($mangaStudio = $manga->studios()->firstWhere('is_studio', '=', true)) {
            $studioMangas = $mangaStudio->manga()
                ->where('model_id', '!=', $manga->id)
                ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);
        } else if ($mangaStudio = $manga->studios()->first()) {
            $studioMangas = $mangaStudio->manga()
                ->where('model_id', '!=', $manga->id)
                ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);;
        }

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $studioMangas->nextPageUrl());

        return JSONResult::success([
            'data' => LiteratureResourceIdentity::collection($studioMangas),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for a Manga item
     *
     * @param RateMangaRequest $request
     * @param Manga $manga
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rateManga(RateMangaRequest $request, Manga $manga): JsonResponse
    {
        $user = auth()->user();

        // Check if the user is already tracking the manga
        if ($user->hasNotTracked($manga)) {
            throw new AuthorizationException(__('Please add ":x" to your library first.', ['x' => $manga->title]));
        }

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];
        $description = $data['description'] ?? null;

        // Try to modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->mangaRatings()
            ->where('model_id', '=', $manga->id)
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
                    'model_id'      => $manga->id,
                    'model_type'    => $manga->getMorphClass(),
                    'rating'        => $givenRating,
                    'description'   => $description,
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Retrieves upcoming Manga results
     *
     * @param GetUpcomingMangaRequest $request
     * @return JsonResponse
     */
    public function upcoming(GetUpcomingMangaRequest $request): JsonResponse
    {
        $data = $request->validated();

        $manga = Manga::upcoming(-1)
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $manga->nextPageUrl());

        return JSONResult::success([
            'data' => LiteratureResourceIdentity::collection($manga),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the reviews of a Manga.
     *
     * @param GetMangaReviewsRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function reviews(GetMangaReviewsRequest $request, Manga $manga): JsonResponse
    {
        $reviews = $manga->mediaRatings()
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
