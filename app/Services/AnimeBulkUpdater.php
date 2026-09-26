<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\MediaStudio;
use App\Models\Studio;
use DB;
use Illuminate\Support\Collection;
use Throwable;

class AnimeBulkUpdater
{
    /**
     * Bulk update the specified attributes for multiple anime.
     *
     * @param Collection $animeIDs
     * @param array      $attributes
     *
     * @return void
     * @throws Throwable
     */
    public static function handle(Collection $animeIDs, array $attributes): void
    {
        DB::transaction(function () use ($animeIDs, $attributes) {
            Anime::withoutGlobalScopes()
                ->whereIn('id', $animeIDs)
                ->update($attributes);

            if (array_key_exists('tv_rating_id', $attributes)) {
                $newRatingID = $attributes['tv_rating_id'];

                AnimeTVRatingPropagator::bulkHandle($animeIDs, $newRatingID);
            }

            if (array_key_exists('tv_rating_id', $attributes) || array_key_exists('is_nsfw', $attributes)) {
                Studio::refreshTVRatings(
                    MediaStudio::where('model_type', '=', Anime::class)
                        ->whereIn('model_id', $animeIDs)
                        ->pluck('studio_id')
                );
            }
        });
    }
}
