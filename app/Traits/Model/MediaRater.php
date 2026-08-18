<?php

namespace App\Traits\Model;

use App\Models\Anime;
use App\Models\MediaRating;
use App\Models\RatingCategory;
use App\Models\RatingCategoryScore;
use App\Support\UserLibraryTouch;
use Illuminate\Database\Eloquent\Collection;
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
     * @param Model $model
     * @param array $attributes
     *
     * @return null|MediaRating
     */
    public function rateMediaModel(Model $model, array $attributes): ?MediaRating
    {
        $morphClass = $model->getMorphClass();
        $modelKey = $model->getKey();
        $categoryScores = $attributes['categoryScores'] ?? [];
        $categoryReviews = $attributes['categoryReviews'] ?? [];
        $ratingCategories = $this->scoredCategoriesFor($morphClass, $categoryScores);
        $isDetailed = $ratingCategories->isNotEmpty();

        $rating = $attributes['rating'] ?? null;
        $description = $attributes['description'] ?? null;

        if ($isDetailed) {
            $rating = $this->weightedRatingFor($ratingCategories, $categoryScores);
            $description = $description ?? $this->composedDescriptionFor($ratingCategories, $categoryReviews);
        }

        $rating = (float) $rating;

        /** @var MediaRating|null $existing */
        $existing = $this->mediaRatings()
            ->where('model_type', '=', $morphClass)
            ->where('model_id', '=', $modelKey)
            ->first();

        if ($existing !== null) {
            if ($rating <= 0 && !$isDetailed) {
                $existing->delete();
                UserLibraryTouch::touch($this->id, $morphClass, [$modelKey]);
                return null;
            }

            $existing->update(array_merge([
                'rating' => $rating,
                'description' => $description ?? $existing->description,
            ], $this->noteAttributeFrom($attributes), $this->spoilerAttributeFrom($attributes), $this->recommendationAttributeFrom($attributes), $this->progressAttributeFrom($model, $description)));
            $this->storeCategoryScores($existing, $ratingCategories, $categoryScores, $categoryReviews);
            UserLibraryTouch::touch($this->id, $morphClass, [$modelKey]);
            return $existing;
        }

        if ($rating > 0 || $isDetailed) {
            /** @var MediaRating $mediaRating */
            $mediaRating = $this->mediaRatings()->create(array_merge([
                'model_type' => $morphClass,
                'model_id' => $modelKey,
                'rating' => $rating,
                'description' => $description,
            ], $this->noteAttributeFrom($attributes), $this->spoilerAttributeFrom($attributes), $this->recommendationAttributeFrom($attributes), $this->progressAttributeFrom($model, $description)));
            $this->storeCategoryScores($mediaRating, $ratingCategories, $categoryScores, $categoryReviews);
            UserLibraryTouch::touch($this->id, $morphClass, [$modelKey]);
            return $mediaRating;
        }

        return null;
    }

    /**
     * Returns the private note to write.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function noteAttributeFrom(array $attributes): array
    {
        if (!array_key_exists('note', $attributes)) {
            return [];
        }

        $note = trim(strip_tags((string) $attributes['note']));

        return ['note' => $note === '' ? null : $note];
    }

    /**
     * Returns the spoiler flag to write.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function spoilerAttributeFrom(array $attributes): array
    {
        if (!array_key_exists('isSpoiler', $attributes)) {
            return [];
        }

        return ['is_spoiler' => (bool) $attributes['isSpoiler']];
    }

    /**
     * Returns the recommendation to write.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function recommendationAttributeFrom(array $attributes): array
    {
        if (!array_key_exists('recommendation', $attributes)) {
            return [];
        }

        return ['recommendation' => (int) $attributes['recommendation']];
    }

    /**
     * Returns the progress snapshot to write alongside a written review.
     *
     * @param Model       $model
     * @param null|string $description
     *
     * @return array
     */
    protected function progressAttributeFrom(Model $model, ?string $description): array
    {
        if (trim((string) $description) === '') {
            return [];
        }

        return ['progress' => $this->progressSnapshotFor($model)];
    }

    /**
     * Returns the user's progress through the model.
     *
     * @param Model $model
     *
     * @return null|int
     */
    public function progressSnapshotFor(Model $model): ?int
    {
        if (!$model instanceof Anime) {
            return null;
        }

        return $this->userWatchedEpisodes()
            ->completed()
            ->whereIn('episode_id', $model->episodes()->select('episodes.id'))
            ->count();
    }

    /**
     * Returns the morph type's rating categories that carry a submitted score, in display order.
     *
     * @param string $morphClass
     * @param array  $categoryScores
     *
     * @return Collection
     */
    protected function scoredCategoriesFor(string $morphClass, array $categoryScores): Collection
    {
        if (empty($categoryScores)) {
            return new Collection();
        }

        return RatingCategory::forModelType($morphClass)
            ->whereIn('id', array_keys($categoryScores))
            ->get();
    }

    /**
     * Returns the weighted average of the submitted category scores, on the media rating scale.
     *
     * @param Collection $ratingCategories
     * @param array      $categoryScores
     *
     * @return float
     */
    protected function weightedRatingFor(Collection $ratingCategories, array $categoryScores): float
    {
        $weightedSum = 0.0;
        $totalWeight = 0.0;

        foreach ($ratingCategories as $ratingCategory) {
            $score = (float) $categoryScores[$ratingCategory->id];
            $score = max(RatingCategoryScore::MIN_SCORE_VALUE, min(RatingCategoryScore::MAX_SCORE_VALUE, $score));

            $weightedSum += $score * $ratingCategory->weight;
            $totalWeight += $ratingCategory->weight;
        }

        if ($totalWeight <= 0) {
            return MediaRating::MIN_RATING_VALUE;
        }

        $scale = RatingCategoryScore::MAX_SCORE_VALUE / MediaRating::MAX_RATING_VALUE;

        return round($weightedSum / $totalWeight / $scale, 2);
    }

    /**
     * Returns the review composed from the submitted per-category reviews.
     *
     * @param Collection $ratingCategories
     * @param array      $categoryReviews
     *
     * @return null|string
     */
    protected function composedDescriptionFor(Collection $ratingCategories, array $categoryReviews): ?string
    {
        $parts = [];

        foreach ($ratingCategories as $ratingCategory) {
            $review = strip_tags(trim((string) ($categoryReviews[$ratingCategory->id] ?? '')));

            if ($review !== '') {
                $parts[] = $ratingCategory->name . ': ' . $review;
            }
        }

        return empty($parts) ? null : implode("\n\n", $parts);
    }

    /**
     * Stores the submitted per-category scores and reviews of the media rating.
     *
     * @param MediaRating $mediaRating
     * @param Collection  $ratingCategories
     * @param array       $categoryScores
     * @param array       $categoryReviews
     *
     * @return void
     */
    protected function storeCategoryScores(MediaRating $mediaRating, Collection $ratingCategories, array $categoryScores, array $categoryReviews): void
    {
        foreach ($ratingCategories as $ratingCategory) {
            $score = (float) $categoryScores[$ratingCategory->id];
            $score = max(RatingCategoryScore::MIN_SCORE_VALUE, min(RatingCategoryScore::MAX_SCORE_VALUE, $score));
            $review = strip_tags(trim((string) ($categoryReviews[$ratingCategory->id] ?? '')));

            RatingCategoryScore::updateOrCreate([
                'rating_id' => $mediaRating->id,
                'rating_category_id' => $ratingCategory->id,
            ], [
                'score' => $score,
                'review' => $review === '' ? null : $review,
            ]);
        }
    }
}
