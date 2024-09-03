<?php

namespace App\Traits\Model;

use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Followable
{
    /**
     * Get the model's followed entries.
     *
     * @return HasMany
     */
    function followable_follows(): HasMany
    {
        return $this->hasMany(UserFollow::class);
    }

    /**
     * The users who followed the model.
     *
     * @return BelongsToMany
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserFollow::class, 'following_user_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Whether the model is followed by the given user.
     *
     * @param User $user
     * @return bool
     */
    public function isFollowedBy(User $user): bool
    {
        $followsLoaded = $this->relationLoaded('followable_follows');

        if ($followsLoaded) {
            return $this->followable_follows->contains($user);
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
     * @return bool
     */
    public function isNotFollowedBy(User $user): bool
    {
        return !$this->isFollowedBy($user);
    }

    /**
     * The number of users who followed the model.
     *
     * @return int
     */
    public function followersCount(): int
    {
        if ($this->followers_count !== null) {
            return (int) $this->followers_count;
        }

        $this->loadCount('followers');

        return (int) $this->followers_count;
    }

    /**
     * The formatted number of users who followed the model.
     *
     * @param int $precision
     * @param bool $abbreviated
     * @return string
     */
    public function followersCountForHumans(int $precision = 1, bool $abbreviated = false): string
    {
        return number_shorten($this->followersCount(), $precision, $abbreviated);
    }

    /**
     * Eloquent builder scope that limits the query to the models followed by the user.
     *
     * @param Builder $query
     * @param Model $user
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
     * @param Model $user
     * @return Builder
     */
    public function scopeWhereNotFollowedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave('followers', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }
}
