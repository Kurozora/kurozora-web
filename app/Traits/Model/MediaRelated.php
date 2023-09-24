<?php

namespace App\Traits\Model;

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
                if ($model->forceDeleting) {
                    $model->mediaRelated()->forceDelete();
                    return;
                }
            }

            $model->mediaRelated()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaRelated()->restore();
            });
        }
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
