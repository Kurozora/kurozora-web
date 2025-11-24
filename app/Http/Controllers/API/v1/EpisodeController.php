<?php

namespace App\Http\Controllers\API\v1;

use App\Events\ModelViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRequest;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\MarkEpisodeAsWatchedRequest;
use App\Http\Requests\RateModelRequest;
use App\Http\Resources\EpisodeResource;
use App\Http\Resources\MediaRatingResource;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\MediaRating;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    /**
     * Returns the information for an episode.
     *
     * @param Request $request
     * @param Episode $episode
     *
     * @return JsonResponse
     */
    public function details(Request $request, Episode $episode): JsonResponse
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($episode, $request->ip());

        $episode->load(['media', 'mediaStat', 'translation', 'tv_rating', 'videos'])
            ->when(auth()->user(), function ($query, $user) use ($episode) {
                $episode->load(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id],
                    ]);
                }])
                    ->loadExists([
                        'user_watched_episodes as isWatched' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }
                    ]);
            });

        // Skeleton in case this logic ever changes.
        // For now both relations are already included,
        // so nothing else needs to be done.
        $includeArray = [];
        if ($includeInput = $request->input('include')) {
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            foreach ($includes as $include) {
                switch ($include) {
                    case 'show':
                        // Already included.
                    case 'season':
                        // Already included.
                        break;
                }
            }
        }

        $includeArray['anime'] = function ($query) {
            $query->withoutGlobalScopes()
                ->with(['media', 'translation'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->withExists([
                        'library as isTracked' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }
                    ]);
                });
        };
        $includeArray['season'] = function ($query) {
            $query->with(['media']);
        };

        $episode->loadMissing($includeArray);

        return JSONResult::success([
            'data' => EpisodeResource::collection([$episode])
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

        $episode = Episode::whereIn('id', $data['ids'] ?? []);
        $episode->with(['media', 'mediaStat', 'translation', 'tv_rating', 'videos'])
            ->when(auth()->user(), function ($query, $user) use ($episode) {
                $episode->with(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id],
                    ]);
                }])
                    ->withExists([
                        'user_watched_episodes as isWatched' => function ($query) use ($user) {
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
                    case 'show':
                        // Already included.
                    case 'season':
                        // Already included.
                        break;
                }
            }
        }

        $includeArray['anime'] = function ($query) {
            $query->withoutGlobalScopes()
                ->with(['media', 'translation'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->withExists([
                        'library as isTracked' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }
                    ]);
                });
        };
        $includeArray['season'] = function ($query) {
            $query->with(['media']);
        };

        $episode->with($includeArray);

        // Show the character details response
        return JSONResult::success([
            'data' => EpisodeResource::collection($episode->get()),
        ]);
    }

    public function suggestions(Episode $episode): JsonResponse
    {
        $title = mb_convert_encoding(substr($episode->title, 0, 20), 'UTF-8', mb_list_encodings());

        $suggestedEpisodes = Episode::search($title)
            ->take(10)
            ->query(function ($query) {
                $query->with([
                    'anime' => function ($query) {
                        $query->withoutGlobalScopes()
                            ->with([
                                'media',
                                'translation',
                            ])
                            ->when(auth()->user(), function ($query, $user) {
                                $query->withExists([
                                    'library as isTracked' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }
                                ]);
                            });
                    },
                    'media',
                    'mediaStat',
                    'season' => function ($query) {
                        $query->withoutGlobalScopes()
                            ->with([
                                'media',
                                'translation'
                            ]);
                    },
                    'tv_rating',
                    'translation',
                    'videos',
                ])
                    ->when(auth()->user(), function ($query, $user) {
                        $query->with(['mediaRatings' => function ($query) use ($user) {
                            $query->where([
                                ['user_id', '=', $user->id],
                            ]);
                        }])
                            ->withExists([
                                'user_watched_episodes as isWatched' => function ($query) use ($user) {
                                    $query->where('user_id', $user->id);
                                },
                            ]);
                    });
            })
            ->get();

        return JSONResult::success([
            'data' => EpisodeResource::collection($suggestedEpisodes)
        ]);
    }

    /**
     * Marks an episode as watched or not watched.
     *
     * @param MarkEpisodeAsWatchedRequest $request
     * @param Episode $episode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function watched(MarkEpisodeAsWatchedRequest $request, Episode $episode): JSONResponse
    {
        $user = auth()->user();
        $anime = $episode->anime()->withoutGlobalScopes()
            ->select([Anime::TABLE_NAME . '.id'])
            ->with([
                'translation'
            ])
            ->first();
        $hasNotTracked = $user->hasNotTracked($anime);

        if ($hasNotTracked) {
            // The item could not be found
            throw new AuthorizationException(__('Please add ":x" to your library first.', ['x' => $anime->title]));
        }

        // Find if the user has watched the episode
        $isAlreadyWatched = $user->hasWatched($episode);

        // If the episode's current status is watched then detach (unwatch) it, otherwise attach (watch) it.
        if ($isAlreadyWatched) {
            $user->episodes()->detach($episode);
        } else {
            $user->episodes()->attach($episode);
        }

        return JSONResult::success([
            'data' => [
                'isWatched' => !$isAlreadyWatched
            ]
        ]);
    }

    /**
     * Adds a rating for an Anime item
     *
     * @param RateModelRequest $request
     * @param Episode $episode
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function rate(RateModelRequest $request, Episode $episode): JsonResponse
    {
        $user = auth()->user();

        // Check if the episode has been watched
        if (!$user->hasWatched($episode)) {
            throw new AuthorizationException(__('Please watch ":x" first.', ['x' => $episode->title]));
        }

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];
        $description = $data['description'] ?? null;

        // Modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->episodeRatings()
            ->withoutTvRatings()
            ->where('model_id', '=', $episode->id)
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
                    'model_id' => $episode->id,
                    'model_type' => $episode->getMorphClass(),
                    'rating' => $givenRating,
                    'description' => $description,
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Delete the user's media rating associated with the given model.
     *
     * @param Episode $episode
     *
     * @return JsonResponse
     */
    public function deleteRating(Episode $episode)
    {
        auth()->user()->mediaRatings()
            ->where([
                ['model_id', '=', $episode->id],
                ['model_type', '=', $episode->getMorphClass()],
            ])
            ->forceDelete();

        return JSONResult::success();
    }

    /**
     * Returns the reviews of an Episode.
     *
     * @param GetPaginatedRequest $request
     * @param Episode $episode
     *
     * @return JsonResponse
     */
    public function reviews(GetPaginatedRequest $request, Episode $episode): JsonResponse
    {
        $reviews = $episode->mediaRatings()
            ->withoutTvRatings()
            ->with([
                'user' => function ($query) {
                    $query->with(['media'])
                        ->withCount(['followers', 'followedModels as following_count', 'mediaRatings']);
                }
            ])
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $reviews->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRatingResource::collection($reviews),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
