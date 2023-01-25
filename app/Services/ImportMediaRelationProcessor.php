<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\KDashboard\MediaRelated as KMediaRelated;
use App\Models\Manga;
use App\Models\MediaRelation;
use App\Models\Relation;
use Illuminate\Database\Eloquent\Collection;

class ImportMediaRelationProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KMediaRelated[] $kMediaRelated
     * @return void
     */
    public function process(Collection|array $kMediaRelated): void
    {
        foreach ($kMediaRelated as $kRelatedMedia) {
            $model = match ($kRelatedMedia->media_type) {
                'manga' => Manga::withoutGlobalScopes()
                    ->firstWhere('mal_id', $kRelatedMedia->media_id),
                default => Anime::withoutGlobalScopes()
                    ->firstWhere('mal_id', $kRelatedMedia->media_id)
            };
            $relatedModel = match ($kRelatedMedia->related_type) {
                'manga' => Manga::withoutGlobalScopes()
                    ->firstWhere('mal_id', $kRelatedMedia->related_id),
                default => Anime::withoutGlobalScopes()
                    ->firstWhere('mal_id', $kRelatedMedia->related_id)
            };
            $relation = Relation::firstWhere('name', str($kRelatedMedia->related->related)->title());

            if (!empty($model) && !empty($relatedModel)) {
                $mediaRelation = MediaRelation::where([
                    ['model_id', '=', $model->id],
                    ['model_type', '=', $model->getMorphClass()],
                    ['relation_id', '=', $relation->id],
                    ['related_id', '=', $relatedModel->id],
                    ['related_type', '=', $relatedModel->getMorphClass()],
                ])->first();

                if (empty($mediaRelation)) {
                    MediaRelation::create([
                        'model_id' => $model->id,
                        'model_type' => $model->getMorphClass(),
                        'relation_id' => $relation->id,
                        'related_id' => $relatedModel->id,
                        'related_type' => $relatedModel->getMorphClass(),
                    ]);
                }
            }
        }
    }
}
