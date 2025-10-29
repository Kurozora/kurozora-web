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
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\PersonResource;
use App\Models\MediaRating;
use App\Models\Person;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class PersonController extends Controller
{
    /**
     * Returns the people index.
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
                    SearchType::People
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
     * Shows person details.
     *
     * @param Request $request
     * @param Person $person
     * @return JsonResponse
     */
    public function details(Request $request, Person $person): JsonResponse
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($person, $request->ip());

        $user = auth()->user();

        $person->load(['media', 'mediaStat'])
            ->when($user, function ($query, $user) use ($person) {
                $person->load(['mediaRatings' => function ($query) use ($user) {
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
                    case 'characters':
                        $includeArray['characters'] = function ($query) {
                            $query->with(['media', 'translation'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'shows':
                        $includeArray['anime'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $person->loadMissing($includeArray);

        // Return person details
        return JSONResult::success([
            'data' => PersonResource::collection([$person])
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

        $person = Person::whereIn('id', $data['ids'] ?? []);
        $person->with(['media', 'mediaStat'])
            ->when(auth()->user(), function ($query, $user) use ($person) {
                $person->with(['mediaRatings' => function ($query) use ($user) {
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
                    case 'characters':
                        $includeArray['characters'] = function ($query) {
                            $query->with(['media', 'translation'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'shows':
                        $includeArray['anime'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $person->with($includeArray);

        // Show the character details response
        return JSONResult::success([
            'data' => PersonResource::collection($person->get()),
        ]);
    }


    /**
     * Returns character information of the person.
     *
     * @param GetPaginatedRequest $request
     * @param Person $person
     * @return JsonResponse
     */
    public function characters(GetPaginatedRequest $request, Person $person): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $person->characters()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl() ?? '');

        // Return person characters
        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns anime information of the person.
     *
     * @param GetPaginatedRequest $request
     * @param Person $person
     * @return JsonResponse
     */
    public function anime(GetPaginatedRequest $request, Person $person): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $anime = $person->anime()
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
     * Returns literatures information of the person.
     *
     * @param GetPaginatedRequest $request
     * @param Person $person
     * @return JsonResponse
     */
    public function literatures(GetPaginatedRequest $request, Person $person): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $literature = $person->manga()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $literature->nextPageUrl() ?? '');

        // Return character literature
        return JSONResult::success([
            'data' => LiteratureResourceIdentity::collection($literature),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns games information of the person.
     *
     * @param GetPaginatedRequest $request
     * @param Person $person
     * @return JsonResponse
     */
    public function games(GetPaginatedRequest $request, Person $person): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $game = $person->games()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $game->nextPageUrl() ?? '');

        // Return character game
        return JSONResult::success([
            'data' => GameResourceIdentity::collection($game),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for a Person item
     *
     * @param RateModelRequest $request
     * @param Person           $person
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function ratePerson(RateModelRequest $request, Person $person): JsonResponse
    {
        $user = auth()->user();

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];
        $description = $data['description'] ?? null;

        // Modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->personRatings()
            ->withoutTvRatings()
            ->where('model_id', '=', $person->id)
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
                    'model_id' => $person->id,
                    'model_type' => $person->getMorphClass(),
                    'rating' => $givenRating,
                    'description' => $description,
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Returns the reviews of a Person.
     *
     * @param GetPaginatedRequest $request
     * @param Person $person
     *
     * @return JsonResponse
     */
    public function reviews(GetPaginatedRequest $request, Person $person): JsonResponse
    {
        $reviews = $person->mediaRatings()
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
