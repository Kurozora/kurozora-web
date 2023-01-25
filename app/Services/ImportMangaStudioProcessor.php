<?php

namespace App\Services;

use App\Enums\StudioType;
use App\Models\Manga;
use App\Models\MediaStudio;
use App\Models\KDashboard\MangaMagazine as KMangaProducer;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Collection;

class ImportMangaStudioProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KMangaProducer[] $kMangaProducers
     * @return void
     */
    public function process(Collection|array $kMangaProducers): void
    {
        foreach ($kMangaProducers as $kMangaProducer) {
            $manga = Manga::withoutGlobalScopes()->firstWhere('mal_id', $kMangaProducer->manga_id);
            $studio = Studio::firstWhere([
                ['type', '=', StudioType::Manga],
                ['mal_id', '=', $kMangaProducer->magazine_id],
            ]);

            if (!empty($studio)) {
                $mangaStudio = MediaStudio::firstWhere([
                    ['model_id', '=', $manga->id],
                    ['model_type', '=', $manga->getMorphClass()],
                    ['studio_id', '=', $studio->id],
                ]);

                if (empty($mangaStudio)) {
                    MediaStudio::create([
                        'model_id' => $manga->id,
                        'model_type' => $manga->getMorphClass(),
                        'studio_id' => $studio->id,
                        'is_licensor' => false,
                        'is_producer' => false,
                        'is_studio' => false,
                        'is_publisher' => true,
                    ]);
                }
            }
        }
    }
}
