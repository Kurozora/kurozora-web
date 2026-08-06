<?php

namespace App\Traits\Model;

use App\Models\Franchise;
use App\Models\MediaFranchise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaFranchises
{
    /**
     * Bootstrap the model with Franchises.
     *
     * @return void
     */
    public static function bootHasMediaFranchises(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaFranchises()->forceDelete();
                    return;
                }
            }

            $model->mediaFranchises()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaFranchises()->restore();
            });
        }
    }

    /**
     * Get the model's media franchises.
     *
     * @return MorphMany
     */
    public function mediaFranchises(): MorphMany
    {
        return $this->morphMany(MediaFranchise::class, 'model');
    }

    /**
     * Get the model's franchises.
     *
     * @return MorphToMany
     */
    public function franchises(): MorphToMany
    {
        return $this->morphToMany(Franchise::class, 'model', MediaFranchise::class)
            ->withTimestamps();
    }
}
