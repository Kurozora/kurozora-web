<?php

namespace App\Http\Controllers;

use App\AnimeSeason;
use App\Helpers\JSONResult;
use Illuminate\Http\Request;

class AnimeSeasonController extends Controller
{
    /**
     * Returns the information for a season
     *
     * @param $seasonID
     */
    public function details($seasonID) {
        // Get the season
        $season = AnimeSeason::find($seasonID);

        // The season does not exist
        if(!$season)
            (new JSONResult())->setError(JSONResult::ERROR_ANIME_SEASON_NON_EXISTENT)->show();

        (new JSONResult())->setData([
            'season' => $season->formatForInfoResponse()
        ])->show();
    }

    /**
     * Returns the episodes for a season
     *
     * @param $seasonID
     */
    public function episodes($seasonID) {
        // Get the season
        $season = AnimeSeason::find($seasonID);

        // The season does not exist
        if(!$seasonID)
            (new JSONResult())->setError(JSONResult::ERROR_ANIME_SEASON_NON_EXISTENT)->show();

        $episodes = $season->getEpisodes();
        $endEpisodes = [];

        foreach($episodes as $episode)
            $endEpisodes[] = $episode->formatEpisodeData();

        (new JSONResult())->setData([
            'season'        => $season->formatForInfoResponse(),
            'episodes'      => $endEpisodes
        ])->show();
    }
}
