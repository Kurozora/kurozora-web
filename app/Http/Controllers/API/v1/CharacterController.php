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
use App\Http\Resources\CharacterResource;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\PersonResourceIdentity;
use App\Models\Character;
use App\Traits\Controller\WithCatalogCacheHeaders;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class CharacterController extends Controller
{
    use WithCatalogCacheHeaders;

    /**
     * Returns the characters index.
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
        $includeInput = $request->input('include');
        $includes = is_string($includeInput) ? explode(',', $includeInput) : (is_array($includeInput) ? $includeInput : []);
        sort($includes);

        $fingerprint = [
            'kind' => 'character',
            'publicId' => $character->public_id,
            'updatedAt' => optional($character->updated_at)->toIso8601String(),
            'locale' => app()->getLocale(),
            'tvRating' => (int) $request->attributes->get('tvRating', 4),
            'include' => $includes,
        ];

        $notModified = $this->returnIfNotModifiedCatalog($request, $fingerprint);
        if ($notModified !== null) {
            return $notModified;
        }

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
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
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

        $character = Character::whereIn('id', $data['ids'] ?? []);
        $character->with(['media', 'mediaStat', 'translation'])
            ->when(auth()->user(), function ($query, $user) use ($character) {
                $character->with(['mediaRatings' => function ($query) use ($user) {
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
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $character->with($includeArray);

        // Show the character details response
        return JSONResult::success([
            'data' => CharacterResource::collection($character->get()),
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
            ->cursorPaginate($data['limit'] ?? 25);

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
            ->cursorPaginate($data['limit'] ?? 25);

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
            ->cursorPaginate($data['limit'] ?? 25);

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
    public function rate(RateModelRequest $request, Character $character): JsonResponse
    {
        $user = auth()->user();

        $data = $request->validated();
        $user->rateMediaModel($character, $data['rating'], $data['description'] ?? null);

        return JSONResult::success();
    }

    /**
     * Delete the user's media rating associated with the given model.
     *
     * @param Character $character
     *
     * @return JsonResponse
     */
    public function deleteRating(Character $character)
    {
        auth()->user()->mediaRatings()
            ->where([
                ['model_id', '=', $character->id],
                ['model_type', '=', $character->getMorphClass()],
            ])
            ->forceDelete();

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
        $data = $request->validated();

        $reviews = $character->mediaRatings()
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
