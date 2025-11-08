<?php

namespace App\Traits\Model;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasBlocking
{
    /**
     * The user's blocked models.
     *
     * @return HasMany
     */
    public function blocked(): HasMany
    {
        return $this->hasMany(UserBlock::class);
    }

    /**
     * The models blocking the user.
     *
     * @return HasMany
     */
    public function blocked_by(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocked_user_id', 'id');
    }

    /**
     * The models blocked by the user.
     *
     * @return BelongsToMany
     */
    public function blockedModels(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserBlock::TABLE_NAME, 'user_id', 'blocked_user_id');
    }

    /**
     * Whether the user has blocked the given model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function hasBlocked(Model $model): bool
    {
        return ($this->relationLoaded('blocked') ? $this->blocked : $this->blocked())
            ->where('blocked_user_id', '=', $model->getKey())
            ->exists();
    }

    /**
     * Whether the user has not blocked the given model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function hasNotBlocked(Model $model): bool
    {
        return !$this->hasBlocked($model);
    }

    /**
     * Block the given model.
     *
     * @param Model $model
     *
     * @return UserBlock
     */
    public function block(Model $model): UserBlock
    {
        $attributes = [
            'user_id' => $this->getKey(),
            'blocked_user_id' => $model->getKey(),
        ];

        return $this->blocked()
            ->where($attributes)
            ->firstOr(function () use ($attributes) {
                $blockedLoaded = $this->relationLoaded('blocked');

                if ($blockedLoaded) {
                    $this->unsetRelation('blocked');
                }

                return $this->blocked()
                    ->create($attributes);
            });
    }

    /**
     * Unblock the given model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function unblock(Model $model): bool
    {
        $hasNotBlocked = $this->hasNotBlocked($model);

        if ($hasNotBlocked) {
            return true;
        }

        $blockedLoaded = $this->relationLoaded('blocked');
        if ($blockedLoaded) {
            $this->unsetRelation('blocked');
        }

        return (bool) $this->blockedModels()
            ->detach($model->getKey());
    }

    /**
     * Clears the blocked models.
     *
     * @return bool
     */
    public function clearBlocked(): bool
    {
        return $this->blocked()
            ->forceDelete();
    }

    /**
     * Toggle block status of the given model.
     *
     * @param Model $model
     *
     * @return UserBlock|bool
     */
    public function toggleBlock(Model $model): bool|UserBlock
    {
        return $this->hasBlocked($model)
            ? $this->unblock($model)
            : $this->block($model);
    }
}
