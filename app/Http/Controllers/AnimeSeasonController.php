<?php

namespace App\Http\Controllers;

use App\Models\AnimeSeason;
use App\Helpers\JSONResult;
use App\Http\Resources\EpisodeResource;
use App\Http\Resources\AnimeSeasonResource;
use Illuminate\Http\JsonResponse;

class AnimeSeasonController extends Controller
{
    /**
     * Returns the information for a season
     *
     * @param AnimeSeason $season
     * @return JsonResponse
     */
    public function details(AnimeSeason $season): JsonResponse
    {
        return JSONResult::success([
            'data' => AnimeSeasonResource::collection([$season])
        ]);
    }

    /**
     * Returns the episodes for a season
     *
     * @param AnimeSeason $season
     * @return JsonResponse
     */
    public function episodes(AnimeSeason $season): JsonResponse
    {
        // Get the episodes
        $episodes = $season->getEpisodes();

        return JSONResult::success([
            'data'  => EpisodeResource::collection($episodes)
        ]);
    }
}
