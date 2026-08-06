<?php

namespace App\Traits\Model;

use App\Models\MediaPlatform;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaPlatforms
{
    /**
     * Bootstrap the model with Platforms.
     *
     * @return void
     */
    public static function bootHasMediaPlatforms(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaPlatforms()->forceDelete();
                    return;
                }
            }

            $model->mediaPlatforms()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaPlatforms()->restore();
            });
        }
    }

    /**
     * Get the model's platform releases.
     *
     * @return MorphMany
     */
    public function mediaPlatforms(): MorphMany
    {
        return $this->morphMany(MediaPlatform::class, 'model');
    }

    /**
     * Get the distinct platforms the model released on.
     *
     * @return MorphToMany
     */
    public function platforms(): MorphToMany
    {
        return $this->morphToMany(Platform::class, 'model', MediaPlatform::class)
            ->withPivot('region', 'release_status', 'released_at')
            ->withTimestamps();
    }
}
