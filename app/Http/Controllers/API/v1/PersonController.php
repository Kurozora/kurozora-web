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
use App\Http\Resources\MediaResource;
use App\Http\Resources\PersonRelationshipResource;
use App\Http\Resources\PersonResource;
use App\Models\Person;
use App\Traits\Controller\WithCatalogCacheHeaders;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class PersonController extends Controller
{
    use WithCatalogCacheHeaders;

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
        $includeInput = $request->input('include');
        $includes = is_string($includeInput) ? explode(',', $includeInput) : (is_array($includeInput) ? $includeInput : []);
        sort($includes);

        $fingerprint = [
            'kind' => 'person',
            'publicId' => $person->public_id,
            'updatedAt' => optional($person->updated_at)->toIso8601String(),
            'locale' => app()->getLocale(),
            'tvRating' => (int) $request->attributes->get('tvRating', 4),
            'include' => $includes,
        ];

        $notModified = $this->returnIfNotModifiedCatalog($request, $fingerprint);
        if ($notModified !== null) {
            return $notModified;
        }

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
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
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
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
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
            ->cursorPaginate($data['limit'] ?? 25);

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
            ->cursorPaginate($data['limit'] ?? 25);

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
            ->cursorPaginate($data['limit'] ?? 25);

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
            ->cursorPaginate($data['limit'] ?? 25);

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
    public function rate(RateModelRequest $request, Person $person): JsonResponse
    {
        $user = auth()->user();

        $data = $request->validated();
        $user->rateMediaModel($person, $data['rating'], $data['description'] ?? null);

        return JSONResult::success();
    }

    /**
     * Delete the user's media rating associated with the given model.
     *
     * @param Person $person
     *
     * @return JsonResponse
     */
    public function deleteRating(Person $person)
    {
        auth()->user()->mediaRatings()
            ->where([
                ['model_id', '=', $person->id],
                ['model_type', '=', $person->getMorphClass()],
            ])
            ->first()?->delete();

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
        $data = $request->validated();

        $reviews = $person->mediaRatings()
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
