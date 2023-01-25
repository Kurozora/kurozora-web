<?php

namespace App\Traits\Model;

use App\Models\MediaGenre;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaGenres
{
    /**
     * Bootstrap the model with Genres.
     *
     * @return void
     */
    public static function bootHasMediaGenres(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->mediaGenres()->delete();
        });
    }

    /**
     * Get the model's genres.
     *
     * @return MorphMany
     */
    public function mediaGenres(): MorphMany
    {
        return $this->morphMany(MediaGenre::class, 'model');
    }

    /**
     * Get the model's genres.
     *
     * @return HasManyThrough
     */
    public function genres(): HasManyThrough
    {
        return $this->hasManyThrough(Genre::class, MediaGenre::class, 'model_id', 'id', 'id', 'genre_id');
    }
}
