<?php

namespace App\Http\Controllers\API\v1;

use App\Events\StudioViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetStudioAnimeRequest;
use App\Http\Requests\GetStudioGamesRequest;
use App\Http\Requests\GetStudioLiteraturesRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\StudioResource;
use App\Models\Studio;
use Illuminate\Http\JsonResponse;

class StudioController extends Controller
{
    /**
     * Shows studio details
     *
     * @param Studio $studio
     * @return JsonResponse
     */
    public function details(Studio $studio): JsonResponse
    {
        // Call the StudioViewed event
        StudioViewed::dispatch($studio);

        // Show studio details
        return JSONResult::success([
            'data' => StudioResource::collection([$studio])
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
        $anime = $studio->getAnime($data['limit'] ?? 25, $data['page'] ?? 1);

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
        $literatures = $studio->getManga($data['limit'] ?? 25, $data['page'] ?? 1);

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
        $games = $studio->getGame($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $games->nextPageUrl());

        return JSONResult::success([
            'data' => GameResourceIdentity::collection($games),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
