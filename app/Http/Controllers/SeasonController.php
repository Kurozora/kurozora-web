<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Helpers\JSONResult;
use App\Http\Resources\EpisodeResource;
use App\Http\Resources\SeasonResource;
use Illuminate\Http\JsonResponse;

class SeasonController extends Controller
{
    /**
     * Returns the information for a season
     *
     * @param Season $season
     * @return JsonResponse
     */
    public function details(Season $season): JsonResponse
    {
        return JSONResult::success([
            'data' => SeasonResource::collection([$season])
        ]);
    }

    /**
     * Returns the episodes for a season
     *
     * @param Season $season
     * @return JsonResponse
     */
    public function episodes(Season $season): JsonResponse
    {
        // Get the episodes
        $episodes = $season->getEpisodes();

        return JSONResult::success([
            'data'  => EpisodeResource::collection($episodes)
        ]);
    }
}
