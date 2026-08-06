<?php

namespace App\Traits\Model;

use App\Models\MediaPlayerPerspective;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaPlayerPerspectives
{
    /**
     * Bootstrap the model with Player Perspectives.
     *
     * @return void
     */
    public static function bootHasMediaPlayerPerspectives(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaPlayerPerspectives()->forceDelete();
                    return;
                }
            }

            $model->mediaPlayerPerspectives()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaPlayerPerspectives()->restore();
            });
        }
    }

    /**
     * Get the model's player perspectives.
     *
     * @return MorphMany
     */
    public function mediaPlayerPerspectives(): MorphMany
    {
        return $this->morphMany(MediaPlayerPerspective::class, 'model');
    }
}
