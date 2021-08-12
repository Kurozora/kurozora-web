<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeStudio;
use App\Models\KDashboard\AnimeProducer as KAnimeProducer;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeStudioProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KAnimeProducer[] $kAnimeProducers
     * @return void
     */
    public function process(Collection|array $kAnimeProducers)
    {
        foreach ($kAnimeProducers as $kAnimeProducer) {
            $anime = Anime::withoutGlobalScope('tv_rating')->firstWhere('mal_id', $kAnimeProducer->anime_id);
            $studio = Studio::firstWhere([
                ['type', 'anime'],
                ['mal_id', $kAnimeProducer->producer_id],
            ]);

            if (!empty($studio)) {
                $animeStudio = AnimeStudio::firstWhere([
                    ['anime_id', $anime->id],
                    ['studio_id', $studio->id],
                ]);

                if (empty($animeStudio)) {
                    AnimeStudio::create([
                        'anime_id' => $anime->id,
                        'studio_id' => $studio->id,
                        'is_licensor' => $kAnimeProducer->is_licensor,
                        'is_producer' => $kAnimeProducer->getIsProducer(),
                        'is_studio' => $kAnimeProducer->is_studio,
                    ]);
                }
            }
        }
    }
}
