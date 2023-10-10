<?php

namespace App\Traits\Model;

use App\Models\Genre;
use App\Models\MediaGenre;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
                if ($model->forceDeleting) {
                    $model->mediaGenres()->forceDelete();
                    return;
                }
            }

            $model->mediaGenres()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaGenres()->restore();
            });
        }
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
     * @return BelongsToMany
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, MediaGenre::class, 'model_id')
            ->where('model_type', '=', $this->getMorphClass())
            ->withTimestamps();
    }

    /**
     * Eloquent builder scope that limits the query to the given genre.
     *
     * @param Builder $query
     * @param int|Genre $genre
     * @return Builder
     */
    public function scopeWhereGenre(Builder $query, int|Genre $genre): Builder
    {
        if (is_numeric($genre)) {
            $genreID = $genre;
        } else {
            $genreID = $genre->id;
        }

        return $query->leftJoin(MediaGenre::TABLE_NAME, MediaGenre::TABLE_NAME . '.model_id', '=', self::TABLE_NAME . '.' . $this->getKeyName())
            ->where(MediaGenre::TABLE_NAME . '.model_type', '=', $this->getMorphClass())
            ->where(MediaGenre::TABLE_NAME . '.genre_id', '=', $genreID)
            ->select(self::TABLE_NAME . '.*');
    }
}
