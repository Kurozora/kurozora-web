<?php

namespace App\Traits\Model;

use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Favorable
{
    /**
     * Bootstrap the model with Favorites.
     *
     * @return void
     */
    public static function bootFavorable(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->favorites()->forceDelete();
                    return;
                }
            }

            $model->favorites()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->favorites()->restore();
            });
        }
    }

    /**
     * Get the model's favorited entries.
     *
     * @return MorphMany
     */
    function favorites(): MorphMany
    {
        return $this->morphMany(UserFavorite::class, 'favorable');
    }

    /**
     * The users who favorited the model.
     *
     * @return BelongsToMany
     */
    public function favoriters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserFavorite::class, 'favorable_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Whether the model is favorited by the given user.
     *
     * @param User $user
     * @return bool
     */
    public function isFavoritedBy(User $user): bool
    {
        $favoritesLoaded = $this->relationLoaded('favorites');

        if ($favoritesLoaded) {
            return $this->favorites->contains($user);
        }

        return (
            $this->relationLoaded('favoriters')
                ? $this->favoriters
                : $this->favoriters()
        )
            ->where('user_id', '=', $user->id)
            ->exists();
    }

    /**
     * Whether the model is not favorited by the given user.
     *
     * @param User $user
     * @return bool
     */
    public function isNotFavoritedBy(User $user): bool
    {
        return !$this->isFavoritedBy($user);
    }

    /**
     * The number of users who favorited the model.
     *
     * @return int
     */
    public function favoritersCount(): int
    {
        if ($this->favoriters_count !== null) {
            return (int) $this->favoriters_count;
        }

        $this->loadCount('favoriters');

        return (int) $this->favoriters_count;
    }

    /**
     * The formatted number of users who favorited the model.
     *
     * @param int $precision
     * @param bool $abbreviated
     * @return string
     */
    public function favoritersCountForHumans(int $precision = 1, bool $abbreviated = false): string
    {
        return number_shorten($this->favoritersCount(), $precision, $abbreviated);
    }

    /**
     * Eloquent builder scope that limits the query to the models favorited by the user.
     *
     * @param Builder $query
     * @param Model $user
     * @return Builder
     */
    public function scopeWhereFavoritedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas('favoriters', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }

    /**
     * Eloquent builder scope that limits the query to the models not favorited by the user.
     *
     * @param Builder $query
     * @param Model $user
     * @return Builder
     */
    public function scopeWhereNotFavoritedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave('favoriters', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }
}
