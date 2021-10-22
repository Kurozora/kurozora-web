<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\KDashboard\MediaRelated as KMediaRelated;
use App\Models\MediaRelation;
use App\Models\Relation;
use Illuminate\Database\Eloquent\Collection;
use Str;

class ImportAnimeRelationProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KMediaRelated[] $kMediaRelated
     * @return void
     */
    public function process(Collection|array $kMediaRelated)
    {
        foreach ($kMediaRelated as $kRelatedMedia) {
            $anime = Anime::withoutGlobalScope('tv_rating')->firstWhere('mal_id', $kRelatedMedia->media_id);
            $relatedAnime = Anime::withoutGlobalScope('tv_rating')->firstWhere('mal_id', $kRelatedMedia->related_id);
            $relation = Relation::firstWhere('name', Str::title($kRelatedMedia->related->related));

            $mediaRelation = MediaRelation::where([
                ['model_id', $anime->id],
                ['model_type', 'anime'],
                ['relation_id', $relation->id],
                ['related_id', $relatedAnime->id],
                ['related_type', 'anime'],
            ])->first();

            if (empty($mediaRelation)) {
                MediaRelation::create([
                    'model_id' => $anime->id,
                    'model_type' => 'anime',
                    'relation_id' => $relation->id,
                    'related_id' => $relatedAnime->id,
                    'related_type' => 'anime',
                ]);
            }
        }
    }
}
