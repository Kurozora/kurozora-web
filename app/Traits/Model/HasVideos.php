<?php

namespace App\Traits\Model;

use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasVideos
{
    /**
     * Bootstrap the model with UUID.
     *
     * @return void
     */
    public static function bootHasVideos(): void
    {
        static::deleting(function (Model $model) {
            $model->videos()->delete();
        });
    }

    /**
     * Get the model's videos.
     *
     * @return MorphMany
     */
    public function videos(): MorphMany
    {
        return $this->morphMany(Video::class, 'videoable');
    }
}
