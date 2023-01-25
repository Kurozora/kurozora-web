<?php

namespace App\Traits\Model;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRelation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait MediaRelated
{
    /**
     * Bootstrap the model with MediaRelation.
     *
     * @return void
     */
    public static function bootMediaRelated(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->mediaRelated()->delete();
        });
    }

    /**
     * Get the model's media relation.
     *
     * @return MorphMany
     */
    public function mediaRelated(): MorphMany
    {
        return $this->morphMany(MediaRelation::class, 'related');
    }
}
