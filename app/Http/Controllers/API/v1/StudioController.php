<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Events\ModelViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetStudioAnimeRequest;
use App\Http\Requests\GetStudioGamesRequest;
use App\Http\Requests\GetStudioLiteraturesRequest;
use App\Http\Requests\GetStudioSuccessorsRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\StudioResource;
use App\Http\Resources\StudioResourceIdentity;
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

        $studio->load(['media']);

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
     * Returns predecessors information of a Studio.
     *
     * @param GetStudioSuccessorsRequest $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function predecessors(GetStudioSuccessorsRequest $request, Studio $studio): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $anime = $studio->predecessors()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl());

        return JSONResult::success([
            'data' => StudioResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns successors information of a Studio.
     *
     * @param GetStudioSuccessorsRequest $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function successors(GetStudioSuccessorsRequest $request, Studio $studio): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $anime = $studio->successor()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl());

        return JSONResult::success([
            'data' => StudioResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns anime information of a Studio.
     *
     * @param GetStudioAnimeRequest $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function anime(GetStudioAnimeRequest $request, Studio $studio): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $anime = $studio->anime()
            ->orderBy('started_at')
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns literatures information of a Studio.
     *
     * @param GetStudioLiteraturesRequest $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function literatures(GetStudioLiteraturesRequest $request, Studio $studio): JsonResponse
    {
        $data = $request->validated();

        // Get the literatures
        $literatures = $studio->manga()
            ->orderBy('started_at')
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $literatures->nextPageUrl());

        return JSONResult::success([
            'data' => LiteratureResourceIdentity::collection($literatures),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns games information of a Studio.
     *
     * @param GetStudioGamesRequest $request
     * @param Studio $studio
     * @return JsonResponse
     */
    public function games(GetStudioGamesRequest $request, Studio $studio): JsonResponse
    {
        $data = $request->validated();

        // Get the games
        $games = $studio->games()
            ->orderBy('published_at')
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $games->nextPageUrl());

        return JSONResult::success([
            'data' => GameResourceIdentity::collection($games),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
