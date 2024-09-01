<?php

namespace App\Traits\Model;

use App\Models\UserFavorite;
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
     * @return MorphToMany
     */
    protected function favoritedModels(string $type): MorphToMany
    {
        return $this->morphedByMany($type, 'favorable', UserFavorite::TABLE_NAME)
            ->withTimestamps();
    }

    /**
     * Whether the user has favorited the given model.
     *
     * @param Model $model
     * @return bool
     */
    public function hasFavorited(Model $model): bool
    {
        return ($this->relationLoaded('favorites') ? $this->favorites : $this->favorites())
            ->where('favorable_id', $model->getKey())
            ->where('favorable_type', $model->getMorphClass())
            ->exists();
    }

    /**
     * Whether the user has not favorited the given model.
     *
     * @param Model $model
     * @return bool
     */
    public function hasNotFavorited(Model $model): bool
    {
        return !$this->hasFavorited($model);
    }

    /**
     * Favorite the given model.
     *
     * @param Model $model
     * @return UserFavorite
     */
    public function favorite(Model $model): UserFavorite
    {
        $attributes = [
            'favorable_id' => $model->getKey(),
            'favorable_type' => $model->getMorphClass(),
        ];

        return $this->favorites()
            ->where($attributes)
            ->firstOr(function () use ($attributes) {
                $favoritesLoaded = $this->relationLoaded('favorites');

                if ($favoritesLoaded) {
                    $this->unsetRelation('favorites');
                }

                return $this->favorites()
                    ->create($attributes);
            });
    }

    /**
     * Unfavorite the given model
     *
     * @param Model $model
     * @return bool
     */
    public function unfavorite(Model $model): bool
    {
        $hasNotFavorited = $this->hasNotFavorited($model);

        if ($hasNotFavorited) {
            return true;
        }

        $favoritesLoaded = $this->relationLoaded('favorites');
        if ($favoritesLoaded) {
            $this->unsetRelation('favorites');
        }

        return (bool) $this->favoritedModels($model::class)
            ->detach($model->getKey());
    }

    /**
     * Clears the favorites of the given type.
     *
     * @param string|null $type
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
     * Toggle favorite status of the given model.
     *
     * @param Model $model
     * @return UserFavorite|bool
     */
    public function toggleFavorite(Model $model): bool|UserFavorite
    {
        return $this->hasFavorited($model)
            ? $this->unfavorite($model)
            : $this->favorite($model);
    }

    /**
     * Eloquent builder scope that limits the query to the models of the specified type.
     *
     * @param string $type
     * @return BelongsToMany
     */
    public function whereFavorited(string $type): BelongsToMany
    {
        return $this->belongsToMany($type, UserFavorite::class, 'user_id', 'favorable_id')
            ->where('favorable_type', '=', $type)
            ->withTimestamps();
    }
}
