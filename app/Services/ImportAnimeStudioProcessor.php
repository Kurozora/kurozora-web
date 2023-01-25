<?php

namespace App\Services;

use App\Enums\StudioType;
use App\Models\Anime;
use App\Models\MediaStudio;
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
    public function process(Collection|array $kAnimeProducers): void
    {
        foreach ($kAnimeProducers as $kAnimeProducer) {
            $anime = Anime::withoutGlobalScopes()->firstWhere('mal_id', $kAnimeProducer->anime_id);
            $studio = Studio::firstWhere([
                ['type', '=', StudioType::Anime],
                ['mal_id', '=', $kAnimeProducer->producer_id],
            ]);

            if (!empty($studio)) {
                $mediaStudio = MediaStudio::firstWhere([
                    ['model_id', '=', $anime->id],
                    ['model_type', '=', $anime->getMorphClass()],
                    ['studio_id', '=', $studio->id],
                ]);

                if (empty($mediaStudio)) {
                    MediaStudio::create([
                        'model_id' => $anime->id,
                        'model_type' => $anime->getMorphClass(),
                        'studio_id' => $studio->id,
                        'is_licensor' => $kAnimeProducer->is_licensor,
                        'is_producer' => $kAnimeProducer->getIsProducer(),
                        'is_studio' => $kAnimeProducer->is_studio,
                        'is_publisher' => false,
                    ]);
                }
            }
        }
    }
}
