<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\StudioResourceBasic;
use App\Studio;
use App\Helpers\JSONResult;
use App\Http\Resources\StudioResource;
use Illuminate\Http\JsonResponse;

class StudioController extends Controller
{
    /**
     * Generate an overview of studios.
     *
     * @return JsonResponse
     */
    public function overview(): JsonResponse
    {
        // Get all studios and format them
        $allStudios = Studio::get()->map(function($studio) {
            return StudioResourceBasic::make($studio);
        });

        // Show studios in response
        return JSONResult::success([
            'data' => $allStudios
        ]);
    }

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
     * @param Studio $studio
     * @return JsonResponse
     */
    public function anime(Studio $studio): JsonResponse
    {
        // Get the anime
        $anime = $studio->getAnime();

        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($anime)
        ]);
    }
}
