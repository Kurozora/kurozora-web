<?php

namespace App\Http\Controllers;

use App\AnimeEpisode;
use App\AnimeSeason;
use App\Helpers\JSONResult;
use App\Http\Resources\AnimeEpisodeResource;
use App\Http\Resources\AnimeSeasonResource;
use App\UserWatchedEpisode;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnimeSeasonController extends Controller
{
    /**
     * Returns the information for a season
     *
     * @param AnimeSeason $season
     * @return JsonResponse
     */
    public function details(AnimeSeason $season) {
        return JSONResult::success([
            'season' => AnimeSeasonResource::make($season)
        ]);
    }

    /**
     * Returns the episodes for a season
     *
     * @param AnimeSeason $season
     * @return JsonResponse
     * @throws \Exception
     */
    public function episodes(AnimeSeason $season) {
        // Get the episodes
        $episodes = $season->getEpisodes();

        return JSONResult::success([
            'episodes'  => AnimeEpisodeResource::collection($episodes)
        ]);
    }
}
