<?php

namespace App\Traits\Model;

use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Follower
{
    /**
     * The user's followed models.
     *
     * @return HasMany
     */
    public function follower_follows(): HasMany
    {
        return $this->hasMany(UserFollow::class);
    }

    /**
     * The models followed by the user.
     *
     * @param string $type
     * @return BelongsToMany
     */
    public function followedModels(string $type = User::class): BelongsToMany
    {
        return $this->belongsToMany($type, UserFollow::class, 'user_id', 'following_user_id')
            ->withTimestamps();
    }

    /**
     * Whether the user has followed the given model.
     *
     * @param Model $model
     * @return bool
     */
    public function hasFollowed(Model $model): bool
    {
        return ($this->relationLoaded('follower_follows') ? $this->follower_follows : $this->follower_follows())
            ->where('user_id', $model->getKey())
            ->exists();
    }

    /**
     * Whether the user has not followed the given model.
     *
     * @param Model $model
     * @return bool
     */
    public function hasNotFollowed(Model $model): bool
    {
        return !$this->hasFollowed($model);
    }

    /**
     * Follow the given model.
     *
     * @param Model $model
     * @return UserFollow
     */
    public function follow(Model $model): UserFollow
    {
        $attributes = [
            'user_id' => $model->getKey(),
        ];

        return $this->follower_follows()
            ->where($attributes)
            ->firstOr(function () use ($attributes) {
                $userFollowsLoaded = $this->relationLoaded('follower_follows');

                if ($userFollowsLoaded) {
                    $this->unsetRelation('follower_follows');
                }

                return $this->follower_follows()
                    ->create($attributes);
            });
    }

    /**
     * Unfollow the given model
     *
     * @param Model $model
     * @return bool
     */
    public function unfollow(Model $model): bool
    {
        $hasNotFollowed = $this->hasNotFollowed($model);

        if ($hasNotFollowed) {
            return true;
        }

        $userFollowsLoaded = $this->relationLoaded('follower_follows');
        if ($userFollowsLoaded) {
            $this->unsetRelation('follower_follows');
        }

        return (bool) $this->followedModels($model::class)
            ->detach($model->getKey());
    }

    /**
     * Clears the follows of the given type.
     *
     * @return bool
     */
    public function clearFollows(): bool
    {
        return $this->follower_follows()
            ->forceDelete();
    }

    /**
     * Toggle follow status of the given model.
     *
     * @param Model $model
     * @return UserFollow|bool
     */
    public function toggleFollow(Model $model): bool|UserFollow
    {
        return $this->hasFollowed($model)
            ? $this->unfollow($model)
            : $this->follow($model);
    }

    /**
     * Eloquent builder scope that limits the query to the models of the specified type.
     *
     * @param string $type
     * @return BelongsToMany
     */
    public function whereFollowed(string $type): BelongsToMany
    {
        return $this->belongsToMany($type, UserFollow::class, 'id', 'user_id')
            ->withTimestamps();
    }
}
