<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Resources\AnimeResourceBasic;
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
