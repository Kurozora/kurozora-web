<?php

namespace App\Traits\Model;

use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Followable
{
    /**
     * Bootstrap the model with Followers.
     *
     * @return void
     */
    public static function bootFollowable(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->followableFollows()->forceDelete();
                    return;
                }
            }

            $model->followableFollows()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->followableFollows()->restore();
            });
        }
    }

    /**
     * Get the model's followed entries.
     *
     * @return MorphMany
     */
    function followableFollows(): MorphMany
    {
        return $this->morphMany(UserFollow::class, 'followable');
    }

    /**
     * The users who followed the model.
     *
     * @return MorphToMany
     */
    public function followers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'followable', UserFollow::TABLE_NAME)
            ->withTimestamps();
    }

    /**
     * Whether the model is followed by the given user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isFollowedBy(User $user): bool
    {
        $followsLoaded = $this->relationLoaded('followableFollows');

        if ($followsLoaded) {
            return $this->followableFollows->contains($user);
        }

        return (
        $this->relationLoaded('followers')
            ? $this->followers
            : $this->followers()
        )
            ->where('user_id', '=', $user->id)
            ->exists();
    }

    /**
     * Whether the model is not followed by the given user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isNotFollowedBy(User $user): bool
    {
        return !$this->isFollowedBy($user);
    }

    /**
     * Eloquent builder scope that limits the query to the models followed by the user.
     *
     * @param Builder $query
     * @param Model   $user
     *
     * @return Builder
     */
    public function scopeWhereFollowedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas('followers', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }

    /**
     * Eloquent builder scope that limits the query to the models not followed by the user.
     *
     * @param Builder $query
     * @param Model   $user
     *
     * @return Builder
     */
    public function scopeWhereNotFollowedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave('followers', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }
}
