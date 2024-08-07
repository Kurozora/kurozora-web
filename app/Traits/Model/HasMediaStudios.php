<?php

namespace App\Traits\Model;

use App\Models\MediaStudio;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaStudios()->forceDelete();
                    return;
                }
            }

            $model->mediaStudios()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaStudios()->restore();
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
     * @return BelongsToMany
     */
    public function studios(): BelongsToMany
    {
        return $this->belongsToMany(Studio::class, MediaStudio::class, 'model_id')
            ->where('model_type', '=', $this->getMorphClass())
            ->using(MediaStudio::class)
            ->withPivot('is_licensor', 'is_producer', 'is_studio', 'is_publisher')
            ->withTimestamps();
    }
}
