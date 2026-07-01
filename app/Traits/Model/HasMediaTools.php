<?php

namespace App\Traits\Model;

use App\Models\MediaTool;
use App\Models\Tool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaTools
{
    /**
     * Bootstrap the model with Tools.
     *
     * @return void
     */
    public static function bootHasMediaTools(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaTools()->forceDelete();
                    return;
                }
            }

            $model->mediaTools()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaTools()->restore();
            });
        }
    }

    /**
     * Get the model's media tools.
     *
     * @return MorphMany
     */
    public function mediaTools(): MorphMany
    {
        return $this->morphMany(MediaTool::class, 'model');
    }

    /**
     * Get the tools the model was made with.
     *
     * @return MorphToMany
     */
    public function tools(): MorphToMany
    {
        return $this->morphToMany(Tool::class, 'model', MediaTool::class)
            ->withTimestamps();
    }
}
