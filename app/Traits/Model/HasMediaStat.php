<?php

namespace App\Traits\Model;

use App\Models\MediaStat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaStat
{
    /**
     * Bootstrap the model with Stat.
     *
     * @return void
     */
    public static function bootHasMediaStat(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->mediaStat()->delete();
        });
    }

    /**
     * Get the model's stat.
     *
     * @return MorphOne
     */
    public function mediaStat(): MorphOne
    {
        return $this->morphOne(MediaStat::class, 'model');
    }
}
