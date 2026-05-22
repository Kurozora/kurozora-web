<?php

namespace App\Traits\Model;

use App\Models\MediaRating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait MediaRater
{
    /**
     * Returns the user's media ratings.
     *
     * @return HasMany
     */
    public function mediaRatings(): HasMany
    {
        return $this->hasMany(MediaRating::class);
    }

    /**
     * Returns the user's media ratings for the given morph type.
     *
     * @param string $modelType
     *
     * @return HasMany
     */
    public function ratingsFor(string $modelType): HasMany
    {
        return $this->mediaRatings()
            ->where('model_type', '=', $modelType);
    }

    /**
     * Returns the user's media ratings that have no description.
     *
     * @return HasMany
     */
    public function mediaRatingsWithoutDescription(): HasMany
    {
        return $this->mediaRatings()
            ->whereNull('description');
    }

    /**
     * Returns the user's media ratings that have a description.
     *
     * @return HasMany
     */
    public function mediaRatingsWithDescription(): HasMany
    {
        return $this->mediaRatings()
            ->whereNotNull('description');
    }

    /**
     * Removes the user's media ratings, optionally limited to a single morph type.
     *
     * @param null|string $type
     *
     * @return bool
     */
    public function clearRatings(?string $type = null): bool
    {
        return $this->mediaRatings()
            ->when($type != null, function ($query) use ($type) {
                $query->where('model_type', '=', $type);
            })
            ->forceDelete();
    }

    /**
     * Records the user's rating for a media model.
     *
     * @param Model       $model
     * @param float       $rating
     * @param null|string $description
     *
     * @return void
     */
    public function rateMediaModel(Model $model, float $rating, ?string $description = null): void
    {
        /** @var MediaRating|null $existing */
        $existing = $this->mediaRatings()
            ->withoutGlobalScopes()
            ->where('model_type', '=', $model->getMorphClass())
            ->where('model_id', '=', $model->getKey())
            ->first();

        if ($existing !== null) {
            if ($rating <= 0) {
                $existing->forceDelete();
                return;
            }

            $existing->update([
                'rating' => $rating,
                'description' => $description ?? $existing->description,
            ]);
            return;
        }

        if ($rating > 0) {
            $this->mediaRatings()->create([
                'model_type' => $model->getMorphClass(),
                'model_id' => $model->getKey(),
                'rating' => $rating,
                'description' => $description,
            ]);
        }
    }
}
