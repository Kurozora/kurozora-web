<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Events\ModelViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRequest;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\RateModelRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\StudioResource;
use App\Http\Resources\StudioResourceIdentity;
use App\Models\MediaRating;
use App\Models\Studio;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class StudioController extends Controller
{
    /**
     * Returns the studios index.
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
                    SearchType::Studios
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
     * Shows studio details
     *
     * @param Request $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function details(Request $request, Studio $studio): JsonResponse
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($studio, $request->ip());

        $studio->load(['media', 'mediaStat', 'tv_rating', 'predecessors', 'successor']);

        $includeArray = [];
        if ($includeInput = $request->input('include')) {
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            foreach ($includes as $include) {
                switch ($include) {
                    case 'shows':
                        $includeArray['anime'] = function ($query) {
                            $query->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $studio->loadMissing($includeArray);

        // Show studio details
        return JSONResult::success([
            'data' => StudioResource::collection([$studio])
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

        $studio = Studio::whereIn('id', $data['ids']);
        $studio->with(['media', 'mediaStat', 'tv_rating', 'predecessors', 'successor']);

        $includeArray = [];
        if ($includeInput = $request->input('include')) {
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            foreach ($includes as $include) {
                switch ($include) {
                    case 'shows':
                        $includeArray['anime'] = function ($query) {
                            $query->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $studio->with($includeArray);

        // Show the character details response
        return JSONResult::success([
            'data' => StudioResource::collection($studio->get()),
        ]);
    }

    /**
     * Returns predecessors information of a Studio.
     *
     * @param GetPaginatedRequest $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function predecessors(GetPaginatedRequest $request, Studio $studio): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $anime = $studio->predecessors()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => StudioResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns successors information of a Studio.
     *
     * @param GetPaginatedRequest $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function successors(GetPaginatedRequest $request, Studio $studio): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $anime = $studio->successor()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => StudioResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns anime information of a Studio.
     *
     * @param GetPaginatedRequest $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function anime(GetPaginatedRequest $request, Studio $studio): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $anime = $studio->anime()
            ->orderBy('started_at')
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns literatures information of a Studio.
     *
     * @param GetPaginatedRequest $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function literatures(GetPaginatedRequest $request, Studio $studio): JsonResponse
    {
        $data = $request->validated();

        // Get the literatures
        $literatures = $studio->manga()
            ->orderBy('started_at')
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $literatures->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => LiteratureResourceIdentity::collection($literatures),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns games information of a Studio.
     *
     * @param GetPaginatedRequest $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function games(GetPaginatedRequest $request, Studio $studio): JsonResponse
    {
        $data = $request->validated();

        // Get the games
        $games = $studio->games()
            ->orderBy('published_at')
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $games->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => GameResourceIdentity::collection($games),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for a studio item
     *
     * @param RateModelRequest $request
     * @param Studio           $studio
     *
     * @return JsonResponse
     */
    public function rateStudio(RateModelRequest $request, Studio $studio): JsonResponse
    {
        $user = auth()->user();

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];
        $description = $data['description'] ?? null;

        // Modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->studioRatings()
            ->withoutTvRatings()
            ->where('model_id', '=', $studio->id)
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
                    'model_id' => $studio->id,
                    'model_type' => $studio->getMorphClass(),
                    'rating' => $givenRating,
                    'description' => $description,
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Returns the reviews of a studio.
     *
     * @param GetPaginatedRequest $request
     * @param Studio                  $studio
     *
     * @return JsonResponse
     */
    public function reviews(GetPaginatedRequest $request, Studio $studio): JsonResponse
    {
        $reviews = $studio->mediaRatings()
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
