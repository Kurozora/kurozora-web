<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Events\ModelViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\RateModelRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\AnimeResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\SongResource;
use App\Models\MediaRating;
use App\Models\Song;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class SongController extends Controller
{
    /**
     * Returns the songs index.
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
                SearchType::Songs
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
     * Shows song details.
     *
     * @param Request $request
     * @param Song    $song
     *
     * @return JsonResponse
     */
    public function view(Request $request, Song $song): JsonResponse
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($song, $request->ip());

        $song->load(['media', 'mediaStat', 'translation'])
            ->when(auth()->user(), function ($query, $user) use ($song) {
                $song->load(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id]
                    ]);
                }]);
            });

        return JSONResult::success([
            'data' => SongResource::collection([$song])
        ]);
    }

    /**
     * Returns anime information for a Song
     *
     * @param GetPaginatedRequest $request
     * @param Song                 $song
     *
     * @return JsonResponse
     */
    public function anime(GetPaginatedRequest $request, Song $song): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $animes = $song->anime()
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
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
            })
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $animes->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => AnimeResource::collection($animes),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns anime information for a Song
     *
     * @param GetPaginatedRequest $request
     * @param Song                $song
     *
     * @return JsonResponse
     */
    public function games(GetPaginatedRequest $request, Song $song): JsonResponse
    {
        $data = $request->validated();

        // Get the games
        $games = $song->games()
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
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
            })
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $games->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => GameResource::collection($games),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for a Song item
     *
     * @param RateModelRequest $request
     * @param Song             $song
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rateSong(RateModelRequest $request, Song $song): JsonResponse
    {
        $user = auth()->user();

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'] ?? null;
        $description = $data['description'] ?? null;

        // Modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->songRatings()
            ->withoutTvRatings()
            ->where('model_id', '=', $song->id)
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
                    'model_id' => $song->id,
                    'model_type' => $song->getMorphClass(),
                    'rating' => $givenRating,
                    'description' => $description
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Returns the reviews of an Song.
     *
     * @param GetPaginatedRequest $request
     * @param Song                  $song
     *
     * @return JsonResponse
     */
    public function reviews(GetPaginatedRequest $request, Song $song): JsonResponse
    {
        $reviews = $song->mediaRatings()
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
        $nextPageURL = str_replace($request->root(), '', $reviews->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRatingResource::collection($reviews),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
