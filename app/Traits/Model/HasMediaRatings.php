<?php

namespace App\Traits\Model;

use App\Models\MediaRating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaRatings
{
    /**
     * Bootstrap the model with Rating.
     *
     * @return void
     */
    public static function bootHasMediaRatings(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaRatings()->forceDelete();
                    return;
                }
            }

            $model->mediaRatings()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaRatings()->restore();
            });
        }
    }

    /**
     * Get the model's ratings.
     *
     * @return MorphMany
     */
    public function mediaRatings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'model');
    }
}
