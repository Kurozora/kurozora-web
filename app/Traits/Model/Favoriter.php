<?php

namespace App\Traits\Model;

use App\Models\UserFavorite;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Favoriter
{
    /**
     * The user's favorited models.
     *
     * @return HasMany
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * The models favorited by the user.
     *
     * @param string $type
     *
     * @return MorphToMany
     */
    protected function favoritedModels(string $type): MorphToMany
    {
        return $this->morphedByMany($type, 'favorable', UserFavorite::TABLE_NAME)
            ->withTimestamps();
    }

    /**
     * Whether the user has favorited the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function hasFavorited(Model|array|Collection $models): bool
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

        return ($this->relationLoaded('favorites') ? $this->favorites : $this->favorites())
                ->where('favorable_type', '=', $modelType)
                ->whereIn('favorable_id', $modelIDs)
                ->count() === count($modelIDs);
    }

    /**
     * Whether the user has not favorited the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function hasNotFavorited(Model|array|Collection $models): bool
    {
        return !$this->hasFavorited($models);
    }

    /**
     * Favorite the given models.
     *
     * @param Model|Model[] $models
     *
     * @return void
     */
    public function favorite(Model|array|Collection $models): void
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return;
        }

        $modelType = $models->first()->getMorphClass();
        $modelKeys = $models->map(fn($model) => $model->getKey());

        $this->favoritedModels($modelType)
            ->attach($modelKeys);
    }

    /**
     * Unfavorite the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function unfavorite(Model|array|Collection $models): bool
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return true;
        }

        if ($this->relationLoaded('favorites')) {
            $this->unsetRelation('favorites');
        }

        $modelType = $models->first()->getMorphClass();
        $modelKeys = $models->map(fn($model) => $model->getKey());

        return (bool) $this->favoritedModels($modelType)
            ->detach($modelKeys);
    }

    /**
     * Clears the favorites of the given type.
     *
     * @param string|null $type
     *
     * @return bool
     */
    public function clearFavorites(?string $type = null): bool
    {
        return $this->favorites()
            ->when($type != null, function ($query) use ($type) {
                $query->where('favorable_type', '=', $type);
            })
            ->forceDelete();
    }

    /**
     * Toggle favorite status of the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function toggleFavorite(Model|array|Collection $models): bool
    {
        if ($this->hasFavorited($models)) {
            $this->unfavorite($models);
            return false;
        } else {
            $this->favorite($models);
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
    public function whereFavorited(string $type): BelongsToMany
    {
        return $this->belongsToMany($type, UserFavorite::class, 'user_id', 'favorable_id')
            ->where('favorable_type', '=', $type)
            ->withTimestamps();
    }
}
