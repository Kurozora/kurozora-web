<?php

namespace App\Traits\Model;

use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Follower
{
    /**
     * The user's followed entries.
     *
     * @return HasMany
     */
    public function followerFollows(): HasMany
    {
        return $this->hasMany(UserFollow::class);
    }

    /**
     * The models followed by the user.
     *
     * @param string $type
     *
     * @return MorphToMany
     */
    protected function followedModels(string $type): MorphToMany
    {
        return $this->morphedByMany($type, 'followable', UserFollow::TABLE_NAME)
            ->withTimestamps();
    }

    /**
     * The users followed by the user.
     *
     * @return MorphToMany
     */
    public function following(): MorphToMany
    {
        return $this->followedModels(User::class);
    }

    /**
     * Whether the user has followed the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function hasFollowed(Model|array|Collection $models): bool
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return false;
        }

        $modelType = $models->first()->getMorphClass();
        $modelIDs = $models->pluck('id')->all();

        return ($this->relationLoaded('followerFollows') ? $this->followerFollows : $this->followerFollows())
                ->where('followable_type', '=', $modelType)
                ->whereIn('followable_id', $modelIDs)
                ->count() === count($modelIDs);
    }

    /**
     * Whether the user has not followed the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function hasNotFollowed(Model|array|Collection $models): bool
    {
        return !$this->hasFollowed($models);
    }

    /**
     * Follow the given models.
     *
     * @param Model|Model[] $models
     *
     * @return void
     */
    public function follow(Model|array|Collection $models): void
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return;
        }

        if ($this->relationLoaded('followerFollows')) {
            $this->unsetRelation('followerFollows');
        }

        $modelType = $models->first()->getMorphClass();
        $modelKeys = $models->map(fn($model) => $model->getKey());

        $this->followedModels($modelType)
            ->attach($modelKeys);
    }

    /**
     * Unfollow the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function unfollow(Model|array|Collection $models): bool
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return true;
        }

        if ($this->relationLoaded('followerFollows')) {
            $this->unsetRelation('followerFollows');
        }

        $modelType = $models->first()->getMorphClass();
        $modelKeys = $models->map(fn($model) => $model->getKey());

        return (bool) $this->followedModels($modelType)
            ->detach($modelKeys);
    }

    /**
     * Clears the follows of the given type.
     *
     * @param string|null $type
     *
     * @return bool
     */
    public function clearFollows(?string $type = null): bool
    {
        return $this->followerFollows()
            ->when($type != null, function ($query) use ($type) {
                $query->where('followable_type', '=', $type);
            })
            ->forceDelete();
    }

    /**
     * Toggle follow status of the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function toggleFollow(Model|array|Collection $models): bool
    {
        if ($this->hasFollowed($models)) {
            $this->unfollow($models);
            return false;
        } else {
            $this->follow($models);
            return true;
        }
    }

    /**
     * Eloquent builder scope that limits the query to the models of the specified type.
     *
     * @param string $type
     *
     * @return BelongsToMany
     */
    public function whereFollowed(string $type): BelongsToMany
    {
        return $this->belongsToMany($type, UserFollow::class, 'user_id', 'followable_id')
            ->where('followable_type', '=', $type)
            ->withTimestamps();
    }
}
