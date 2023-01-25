<?php

namespace App\Traits\Model;

use App\Models\MediaTheme;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaThemes
{
    /**
     * Bootstrap the model with Themes.
     *
     * @return void
     */
    public static function bootHasMediaThemes(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->mediaThemes()->delete();
        });
    }

    /**
     * Get the model's themes.
     *
     * @return MorphMany
     */
    public function mediaThemes(): MorphMany
    {
        return $this->morphMany(MediaTheme::class, 'model');
    }

    /**
     * Get the model's themes.
     *
     * @return HasManyThrough
     */
    public function themes(): HasManyThrough
    {
        return $this->hasManyThrough(Theme::class, MediaTheme::class, 'model_id', 'id', 'id', 'theme_id');
    }
}
