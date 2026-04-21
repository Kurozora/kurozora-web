<?php

namespace App\Traits\Model;

use App\Models\MediaRating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaRatings
{
    /**
     * Bootstrap the model with Rating.
     *
     * @return void
     */
    public static function bootHasMediaRatings(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaRatings()->forceDelete();
                    return;
                }
            }

            $model->mediaRatings()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaRatings()->restore();
            });
        }
    }

    /**
     * Get the model's ratings.
     *
     * @return MorphMany
     */
    public function mediaRatings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'model');
    }

    /**
     * Ratings that include a written review.
     *
     * @return MorphMany
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'model')
            ->whereNotNull('description');
    }

    /**
     * Detailed (per-category) ratings only.
     *
     * @return MorphMany
     */
    public function detailedRatings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'model')
            ->where('rating_style', \App\Enums\RatingStyle::Detailed()->value);
    }

    // -----------------------------------------------------------------------
    // Convenience helpers
    // -----------------------------------------------------------------------

    /**
     * Return the MediaRating for a specific user, or null if they haven't rated yet.
     *
     * @param  int  $userId
     * @return MediaRating|null
     */
    public function userRating(int $userId): ?MediaRating
    {
        return $this->ratings()
            ->where('user_id', $userId)
            ->first();
    }
}
