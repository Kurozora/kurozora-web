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
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->mediaRatings()->delete();
        });
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
