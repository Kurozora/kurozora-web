<?php

namespace App\Traits\Model;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserBlockable
{
    /**
     * Get the model's blocked entries.
     *
     * @return HasMany
     */
    function user_blocks(): HasMany
    {
        return $this->hasMany(UserBlock::class);
    }

    /**
     * The users who blocked the model.
     *
     * @return BelongsToMany
     */
    public function blockers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserBlock::class, 'blocked_user_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Whether the model is blocked by the given user.
     *
     * @param User $user
     * @return bool
     */
    public function isBlockedBy(User $user): bool
    {
        $relationLoaded = $this->relationLoaded('user_blocks');

        if ($relationLoaded) {
            return $this->user_blocks->contains($user);
        }

        return (
        $this->relationLoaded('blockers')
            ? $this->blockers
            : $this->blockers()
        )
            ->where('user_id', '=', $user->id)
            ->exists();
    }

    /**
     * Whether the model is not blocked by the given user.
     *
     * @param User $user
     * @return bool
     */
    public function isNotBlockedBy(User $user): bool
    {
        return !$this->isBlockedBy($user);
    }

    /**
     * The number of users who blocked the model.
     *
     * @return int
     */
    public function blockersCount(): int
    {
        if ($this->blockers_count !== null) {
            return (int) $this->blockers_count;
        }

        $this->loadCount('blockers');

        return (int) $this->blockers_count;
    }

    /**
     * The formatted number of users who blocked the model.
     *
     * @param int $precision
     * @param bool $abbreviated
     * @return string
     */
    public function blockersCountForHumans(int $precision = 1, bool $abbreviated = false): string
    {
        return number_shorten($this->blockersCount(), $precision, $abbreviated);
    }

    /**
     * Eloquent builder scope that limits the query to the models blocked by the user.
     *
     * @param Builder $query
     * @param Model $user
     * @return Builder
     */
    public function scopeWhereBlockedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas('blockers', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }

    /**
     * Eloquent builder scope that limits the query to the models not blocked by the user.
     *
     * @param Builder $query
     * @param Model $user
     * @return Builder
     */
    public function scopeWhereNotBlockedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave('blockers', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }
}
