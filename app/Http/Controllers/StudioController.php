<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetStudioAnimeRequest;
use App\Http\Resources\AnimeResourceIdentity;
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
}
