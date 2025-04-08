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
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResource;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\PersonResourceIdentity;
use App\Models\Character;
use App\Models\MediaRating;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class CharacterController extends Controller
{
    /**
     * Returns the characters index.
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
                SearchType::Characters
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
     * Shows character details.
     *
     * @param Request $request
     * @param Character $character
     *
     * @return JsonResponse
     */
    public function details(Request $request, Character $character): JsonResponse
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($character, $request->ip());

        $user = auth()->user();

        $character->load(['media', 'mediaStat', 'translation'])
            ->when($user, function ($query, $user) use ($character) {
                $character->load(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id]
                    ]);
                }]);
            });

        $includeArray = [];
        if ($includeInput = $request->input('include')) {
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            foreach ($includes as $include) {
                switch ($include) {
                    case 'people':
                        $includeArray['people'] = function ($query) {
                            $query->with(['media'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'shows':
                        $includeArray['anime'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $character->loadMissing($includeArray);

        // Return character details
        return JSONResult::success([
            'data' => CharacterResource::collection([$character])
        ]);
    }

    /**
     * Returns person information about a character.
     *
     * @param GetPaginatedRequest $request
     * @param Character $character
     *
     * @return JsonResponse
     */
    public function people(GetPaginatedRequest $request, Character $character): JsonResponse
    {
        $data = $request->validated();

        // Get the people
        $people = $character->people()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $people->nextPageUrl() ?? '');

        // Return character people
        return JSONResult::success([
            'data' => PersonResourceIdentity::collection($people),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns anime information about a character.
     *
     * @param GetPaginatedRequest $request
     * @param Character $character
     *
     * @return JsonResponse
     */
    public function anime(GetPaginatedRequest $request, Character $character): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $anime = $character->anime()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl() ?? '');

        // Return character anime
        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns literatures information about a character.
     *
     * @param GetPaginatedRequest $request
     * @param Character $character
     *
     * @return JsonResponse
     */
    public function literatures(GetPaginatedRequest $request, Character $character): JsonResponse
    {
        $data = $request->validated();

        // Get the literatures
        $literatures = $character->manga()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $literatures->nextPageUrl() ?? '');

        // Return character literatures
        return JSONResult::success([
            'data' => LiteratureResourceIdentity::collection($literatures),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns games information about a character.
     *
     * @param GetPaginatedRequest $request
     * @param Character $character
     *
     * @return JsonResponse
     */
    public function games(GetPaginatedRequest $request, Character $character): JsonResponse
    {
        $data = $request->validated();

        // Get the games
        $games = $character->games()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $games->nextPageUrl() ?? '');

        // Return character games
        return JSONResult::success([
            'data' => GameResourceIdentity::collection($games),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for a Character item
     *
     * @param RateModelRequest $request
     * @param Character        $character
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rateCharacter(RateModelRequest $request, Character $character): JsonResponse
    {
        $user = auth()->user();

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];
        $description = $data['description'] ?? null;

        // Modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->characterRatings()
            ->withoutTvRatings()
            ->where('model_id', '=', $character->id)
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
                    'model_id' => $character->id,
                    'model_type' => $character->getMorphClass(),
                    'rating' => $givenRating,
                    'description' => $description,
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Returns the reviews of a Character.
     *
     * @param GetPaginatedRequest $request
     * @param Character           $character
     *
     * @return JsonResponse
     */
    public function reviews(GetPaginatedRequest $request, Character $character): JsonResponse
    {
        $reviews = $character->mediaRatings()
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
