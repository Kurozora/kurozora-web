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
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->mediaStaff()->delete();
        });
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
        return $this->belongsToMany(Person::class, MediaStaff::TABLE_NAME, 'model_id')
            ->withTimestamps();
    }
}
