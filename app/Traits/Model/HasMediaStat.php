<?php

namespace App\Traits\Model;

use App\Models\MediaStat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaStat
{
    // Minimum ratings required to calculate average
    const int MINIMUM_RATINGS_REQUIRED = 1;

    /**
     * Bootstrap the model with Stat.
     *
     * @return void
     */
    public static function bootHasMediaStat(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaStat()->forceDelete();
                    return;
                }
            }

            $model->mediaStat()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaStat()->restore();
            });
        }

        static::created(function (Model $model) {
            $model->mediaStat()->create();
        });
    }

    /**
     * Get the model's stat.
     *
     * @return MorphOne
     */
    public function mediaStat(): MorphOne
    {
        return $this->morphOne(MediaStat::class, 'model');
    }
}
