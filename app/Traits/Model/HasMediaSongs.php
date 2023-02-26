<?php

namespace App\Traits\Model;

use App\Models\MediaSong;
use App\Models\Song;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaSongs
{
    /**
     * Bootstrap the model with Songs.
     *
     * @return void
     */
    public static function bootHasMediaSongs(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->mediaSongs()->delete();
        });
    }

    /**
     * Get the model's songs.
     *
     * @return MorphMany
     */
    public function mediaSongs(): MorphMany
    {
        return $this->morphMany(MediaSong::class, 'model');
    }

    /**
     * Get the model's songs.
     *
     * @return BelongsToMany
     */
    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, MediaSong::class, 'model_id')
            ->withTimestamps();
    }
}
