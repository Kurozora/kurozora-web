<?php

namespace App\Traits\Model;

use App\Models\MediaRating;
use App\Support\UserLibraryTouch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Client\ConnectionException;

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
     * @throws ConnectionException
     */
    public function clearRatings(?string $type = null): bool
    {
        $affected = (bool) $this->mediaRatings()
            ->when($type != null, function ($query) use ($type) {
                $query->where('model_type', '=', $type);
            })
            ->delete();

        UserLibraryTouch::touchAll($this->id, $type);

        return $affected;
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
        $morphClass = $model->getMorphClass();
        $modelKey = $model->getKey();

        /** @var MediaRating|null $existing */
        $existing = $this->mediaRatings()
            ->where('model_type', '=', $morphClass)
            ->where('model_id', '=', $modelKey)
            ->first();

        if ($existing !== null) {
            if ($rating <= 0) {
                $existing->delete();
                UserLibraryTouch::touch($this->id, $morphClass, [$modelKey]);
                return;
            }

            $existing->update([
                'rating' => $rating,
                'description' => $description ?? $existing->description,
            ]);
            UserLibraryTouch::touch($this->id, $morphClass, [$modelKey]);
            return;
        }

        if ($rating > 0) {
            $this->mediaRatings()->create([
                'model_type' => $morphClass,
                'model_id' => $modelKey,
                'rating' => $rating,
                'description' => $description,
            ]);
            UserLibraryTouch::touch($this->id, $morphClass, [$modelKey]);
        }
    }
}
