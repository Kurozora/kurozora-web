<?php

namespace App\Http\Controllers;

use App\AnimeEpisode;
use App\AnimeSeason;
use App\Helpers\JSONResult;
use App\UserWatchedEpisode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function episodes(Request $request, $seasonID) {
        // Get the season
        $season = AnimeSeason::find($seasonID);

        // The season does not exist
        if(!$seasonID)
            (new JSONResult())->setError(JSONResult::ERROR_ANIME_SEASON_NON_EXISTENT)->show();

        // Determine columns to select
        $columnsToSelect = [
            AnimeEpisode::TABLE_NAME . '.id AS episode_id',
            AnimeEpisode::TABLE_NAME . '.number AS number',
            AnimeEpisode::TABLE_NAME . '.name AS name',
            AnimeEpisode::TABLE_NAME . '.first_aired AS first_aired',
            AnimeEpisode::TABLE_NAME . '.overview AS overview',
            AnimeEpisode::TABLE_NAME . '.verified AS verified',
            // Select the user's "watched" status via subquery
            DB::raw('(SELECT COUNT(*) FROM ' . UserWatchedEpisode::TABLE_NAME . ' WHERE episode_id = ' . AnimeEpisode::TABLE_NAME . '.id AND user_id = ' . $request->user_id . ' LIMIT 1) AS watched')
        ];

        // Get the episodes
        $episodeInfo = DB::table(AnimeEpisode::TABLE_NAME)
            ->select($columnsToSelect)
            ->where([
                [AnimeEpisode::TABLE_NAME . '.season_id', '=', $seasonID]
            ])
            ->orderBy('number', 'ASC');

        $rawEpisodes = $episodeInfo->get();

        // Format the episodes
        $endEpisodes = [];

        foreach($rawEpisodes as $rawEpisode) {
            $firstAiredUnix = (new Carbon($rawEpisode->first_aired))->timestamp;
            $formattedFirstAired = date('j M, Y', $firstAiredUnix);

            $endEpisodes[] = [
                'id'            => $rawEpisode->episode_id,
                'number'        => $rawEpisode->number,
                'name'          => $rawEpisode->name,
                'first_aired'   => $formattedFirstAired,
                'overview'      => $rawEpisode->overview,
                'verified'      => (bool) $rawEpisode->verified,
                'user_details'  => [
                    'watched'   => (bool) $rawEpisode->watched
                ]
            ];
        }

        // Show the response
        (new JSONResult())->setData([
            'season'        => $season->formatForInfoResponse(),
            'episodes'      => $endEpisodes
        ])->show();
    }
}
