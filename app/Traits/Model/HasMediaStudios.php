<?php

namespace App\Traits\Model;

use App\Models\MediaStudio;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaStudios
{
    /**
     * Bootstrap the model with Studios.
     *
     * @return void
     */
    public static function bootHasMediaStudios(): void
    {
        static::updated(function (Model $model) {
            if ($model->wasChanged(['tv_rating_id', 'is_nsfw'])) {
                Studio::refreshTVRatings($model->mediaStudios()->pluck('studio_id'), $model->getConnectionName());
            }
        });

        static::deleting(function (Model $model) {
            $studioIDs = $model->mediaStudios()->pluck('studio_id');

            if (in_array(SoftDeletes::class, class_uses_recursive($model)) && $model->forceDeleting) {
                $model->mediaStudios()->forceDelete();
            } else {
                $model->mediaStudios()->delete();
            }

            Studio::refreshTVRatings($studioIDs, $model->getConnectionName());
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaStudios()->restore();
            });

            static::restored(function (Model $model) {
                Studio::refreshTVRatings($model->mediaStudios()->pluck('studio_id'), $model->getConnectionName());
            });
        }
    }

    /**
     * Get the model's studios.
     *
     * @return MorphMany
     */
    public function mediaStudios(): MorphMany
    {
        return $this->morphMany(MediaStudio::class, 'model');
    }

    /**
     * Get the model's studios.
     *
     * @return MorphToMany
     */
    public function studios(): MorphToMany
    {
        return $this->viewableViaParent(
            $this->morphToMany(Studio::class, 'model', MediaStudio::class)
                ->withPivot('is_licensor', 'is_producer', 'is_studio', 'is_publisher')
                ->withTimestamps(),
        );
    }
}
