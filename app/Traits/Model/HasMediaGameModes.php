<?php

namespace App\Traits\Model;

use App\Models\MediaGameMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaGameModes
{
    /**
     * Bootstrap the model with Game Modes.
     *
     * @return void
     */
    public static function bootHasMediaGameModes(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaGameModes()->forceDelete();
                    return;
                }
            }

            $model->mediaGameModes()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaGameModes()->restore();
            });
        }
    }

    /**
     * Get the model's game modes.
     *
     * @return MorphMany
     */
    public function mediaGameModes(): MorphMany
    {
        return $this->morphMany(MediaGameMode::class, 'model');
    }
}
