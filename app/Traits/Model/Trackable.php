<?php

namespace App\Traits\Model;

use App\Models\User;
use App\Models\UserLibrary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Trackable
{
    /**
     * Bootstrap the model with Tracking.
     *
     * @return void
     */
    public static function bootTrackable(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->library()->forceDelete();
                    return;
                }
            }

            $model->library()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->library()->restore();
            });
        }
    }

    /**
     * Get the model's library entries.
     *
     * @return MorphMany
     */
    function library(): MorphMany
    {
        return $this->morphMany(UserLibrary::class, 'trackable');
    }

    /**
     * The users who tracked the model.
     *
     * @return MorphToMany
     */
    public function trackers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'trackable', UserLibrary::TABLE_NAME)
            ->withTimestamps();
    }

    /**
     * Whether the model is tracked by the given user.
     *
     * @param User $user
     * @return bool
     */
    public function isTrackedBy(User $user): bool
    {
        $libraryLoaded = $this->relationLoaded('library');

        if ($libraryLoaded) {
            return $this->library->contains($user);
        }

        return (
            $this->relationLoaded('trackers')
                ? $this->trackers
                : $this->trackers()
        )
            ->where('user_id', '=', $user->id)
            ->exists();
    }

    /**
     * Whether the model is not tracked by the given user.
     *
     * @param User $user
     * @return bool
     */
    public function isNotTrackedBy(User $user): bool
    {
        return !$this->isTrackedBy($user);
    }

    /**
     * Eloquent builder scope that limits the query to the models tracked by the user.
     *
     * @param Builder $query
     * @param Model $user
     * @return Builder
     */
    public function scopeWhereTrackedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas('trackers', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }

    /**
     * Eloquent builder scope that limits the query to the models not tracked by the user.
     *
     * @param Builder $query
     * @param Model $user
     * @return Builder
     */
    public function scopeWhereNotTrackedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave('trackers', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }
}
