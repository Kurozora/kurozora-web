<?php

namespace App\Traits\Model;

use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasVideos
{
    /**
     * Bootstrap the model with Videos.
     *
     * @return void
     */
    public static function bootHasVideos(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->videos()->forceDelete();
                    return;
                }
            }

            $model->videos()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->videos()->restore();
            });
        }
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
