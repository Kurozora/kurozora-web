<?php

namespace App\Traits\Model;

use App\Models\MediaStaff;
use App\Models\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaStaff
{
    /**
     * Bootstrap the model with Staff.
     *
     * @return void
     */
    public static function bootHasMediaStaff(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaStaff()->forceDelete();
                    return;
                }
            }

            $model->mediaStaff()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaStaff()->restore();
            });
        }
    }

    /**
     * Get the model's staff.
     *
     * @return MorphMany
     */
    public function mediaStaff(): MorphMany
    {
        return $this->morphMany(MediaStaff::class, 'model');
    }

    /**
     * Get the model's people.
     *
     * @return BelongsToMany
     */
    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, MediaStaff::class, 'model_id')
            ->withTimestamps();
    }
}
