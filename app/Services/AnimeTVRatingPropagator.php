<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use DB;
use Illuminate\Support\Collection;
use Throwable;

class AnimeTVRatingPropagator
{
    /**
     * Propagate the TV rating change from the anime to its seasons and episodes.
     *
     * @param Anime $anime
     *
     * @return void
     * @throws Throwable
     */
    public static function handle(Anime $anime): void
    {
        if (!$anime->wasChanged('tv_rating_id')) {
            return;
        }

        $newRatingID = $anime->tv_rating_id;

        DB::transaction(function () use ($anime, $newRatingID) {
            // Update seasons
            Season::where('anime_id', $anime->id)
                ->update(['tv_rating_id' => $newRatingID]);

            // Update episodes
            Episode::whereIn('season_id', function ($query) use ($anime) {
                $query->select('id')
                    ->from('seasons')
                    ->where('anime_id', $anime->id);
            })
                ->update(['tv_rating_id' => $newRatingID]);

            // Re-index
            Season::where('anime_id', $anime->id)->searchable();

            Episode::whereIn('season_id', function ($query) use ($anime) {
                $query->select('id')
                    ->from('seasons')
                    ->where('anime_id', $anime->id);
            })
                ->searchable();
        });
    }

    /**
     * Bulk propagate the TV rating change from multiple anime to their seasons and episodes.
     *
     * @param Collection $animeIDs
     * @param int        $newRatingID
     *
     * @return void
     * @throws Throwable
     */
    public static function bulkHandle(Collection $animeIDs, int $newRatingID): void
    {
        DB::transaction(function () use ($animeIDs, $newRatingID) {
            // Update seasons
            Season::whereIn('anime_id', $animeIDs)
                ->update(['tv_rating_id' => $newRatingID]);

            // Update episodes
            Episode::whereIn('season_id', function ($query) use ($animeIDs) {
                $query->select('id')
                    ->from('seasons')
                    ->whereIn('anime_id', $animeIDs);
            })
                ->update(['tv_rating_id' => $newRatingID]);

            // Re-index
            Season::whereIn('anime_id', $animeIDs)->searchable();

            Episode::whereIn('season_id', function ($query) use ($animeIDs) {
                $query->select('id')
                    ->from('seasons')
                    ->whereIn('anime_id', $animeIDs);
            })
                ->searchable();
        });
    }
}
