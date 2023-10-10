<?php

namespace App\Traits\Model;

use App\Models\MediaTheme;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
                if ($model->forceDeleting) {
                    $model->mediaThemes()->forceDelete();
                    return;
                }
            }

            $model->mediaThemes()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaThemes()->restore();
            });
        }
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
     * @return BelongsToMany
     */
    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class, MediaTheme::class, 'model_id')
            ->where('model_type', '=', $this->getMorphClass())
            ->withTimestamps();
    }

    /**
     * Eloquent builder scope that limits the query to the given theme.
     *
     * @param Builder $query
     * @param string|int|Theme $theme
     * @return Builder
     */
    public function scopeWhereTheme(Builder $query, string|int|Theme $theme): Builder
    {
        if (is_numeric($theme)) {
            $themeID = $theme;
        } else {
            $themeID = $theme->id;
        }

        return $query->leftJoin(MediaTheme::TABLE_NAME, MediaTheme::TABLE_NAME . '.model_id', '=', self::TABLE_NAME . '.' . $this->getKeyName())
            ->where(MediaTheme::TABLE_NAME . '.model_type', '=', $this->getMorphClass())
            ->where(MediaTheme::TABLE_NAME . '.theme_id', '=', $themeID)
            ->select(self::TABLE_NAME . '.*');
    }
}
