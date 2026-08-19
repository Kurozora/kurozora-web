<?php

namespace App\Models;

use App\Enums\FavoriteKind;
use App\Enums\FavoriteSentiment;
use App\Enums\RatingSentiment;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaStat extends KModel
{
    use SoftDeletes;

    // Sentiment gap boundaries, in percentiles
    const float NARROW_SENTIMENT_GAP = 0.10;
    const float WIDE_SENTIMENT_GAP = 0.30;

    // Sample sizes the strongest sentiments require
    const int CONFIDENT_RATING_COUNT = 50;
    const int ACCLAIM_RATING_COUNT = 500;

    // Audience sizes a favorite sentiment requires
    const int MINIMUM_FAVORITE_SAMPLE = 10;
    const int CONFIDENT_FAVORITE_SAMPLE = 30;

    // Table name
    const string TABLE_NAME = 'media_stats';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the model of the media stat.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $mediaStat = $this->toArray();
        unset($mediaStat['created_at']);
        unset($mediaStat['updated_at']);
        unset($mediaStat['deleted_at']);
        return $mediaStat;
    }

    /**
     * The percentage of ratings that are positive.
     *
     * @return float|int
     */
    public function getPositivePercentageAttribute(): float|int
    {
        $ratingTotal = max($this->rating_count, 1);

        return 100 / $ratingTotal * $this->positiveRatingCount();
    }

    /**
     * The number of ratings at or above the positive threshold.
     *
     * @return int
     */
    private function positiveRatingCount(): int
    {
        return $this->rating_7 + $this->rating_8 + $this->rating_9 + $this->rating_10;
    }

    /**
     * The audience the model's favorite share is measured against.
     *
     * @return int
     */
    public function getFavoriteAudienceAttribute(): int
    {
        // Library-trackable kinds gate favoriting behind a library entry, so everyone who
        // favorited is someone who tracked it. The rest have no library, leaving raters.
        return FavoriteKind::fromMorphClass($this->model_type)?->isLibraryTrackable()
            ? (int) $this->model_count
            : (int) $this->rater_count;
    }

    /**
     * The portion of the model's audience that favorited it.
     *
     * @return float
     */
    public function getFavoriteShareAttribute(): float
    {
        $audience = $this->favorite_audience;

        if ($audience <= 0) {
            return 0.0;
        }

        $favorites = FavoriteKind::fromMorphClass($this->model_type)?->isLibraryTrackable()
            ? $this->favorite_count
            : $this->rater_favorite_count;

        return min($favorites / $audience, 1.0);
    }

    /**
     * The label describing how much the model is liked next to how it is rated.
     *
     * @return string
     */
    public function getFavoriteSentimentAttribute(): string
    {
        $audience = $this->favorite_audience;

        if ($audience < self::MINIMUM_FAVORITE_SAMPLE) {
            return FavoriteSentiment::NotEnough()->description;
        }

        // The outer bands claim a lot, so they ask for an audience worth claiming it about.
        $hasConfidentSample = $audience >= self::CONFIDENT_FAVORITE_SAMPLE;

        return match (true) {
            $hasConfidentSample && $this->sentiment_gap >= self::WIDE_SENTIMENT_GAP => FavoriteSentiment::GuiltyPleasure()->description,
            $this->sentiment_gap >= self::NARROW_SENTIMENT_GAP => FavoriteSentiment::FanFavorite()->description,
            $hasConfidentSample && $this->sentiment_gap <= -self::WIDE_SENTIMENT_GAP => FavoriteSentiment::RarelyLiked()->description,
            $this->sentiment_gap <= -self::NARROW_SENTIMENT_GAP => FavoriteSentiment::NotWidelyLiked()->description,
            default => FavoriteSentiment::WidelyLiked()->description,
        };
    }

    /**
     * The sentiment label.
     *
     * @return string
     */
    public function getSentimentAttribute(): string
    {
        $totalRatings = $this->rating_1 + $this->rating_2 + $this->rating_3 + $this->rating_4
            + $this->rating_5 + $this->rating_6 + $this->rating_7 + $this->rating_8
            + $this->rating_9 + $this->rating_10;

        // Check if there are no ratings yet
        if ($totalRatings === 0) {
            return RatingSentiment::NotEnough()->description;
        }

        $positivePercentage = 100 / $totalRatings * $this->positiveRatingCount();

        // The higher tiers each demand a larger sample, so the first match wins.
        return match (true) {
            $positivePercentage >= 95 && $totalRatings >= self::ACCLAIM_RATING_COUNT => RatingSentiment::OverwhelminglyPositive()->description,
            $positivePercentage >= 85 && $totalRatings >= self::CONFIDENT_RATING_COUNT => RatingSentiment::VeryPositive()->description,
            $positivePercentage >= 80 => RatingSentiment::Positive()->description,
            $positivePercentage >= 70 => RatingSentiment::MostlyPositive()->description,
            $positivePercentage >= 40 => RatingSentiment::MixedFeelings()->description,
            $positivePercentage >= 20 => RatingSentiment::MostlyNegative()->description,
            $totalRatings >= self::ACCLAIM_RATING_COUNT => RatingSentiment::OverwhelminglyNegative()->description,
            $totalRatings >= self::CONFIDENT_RATING_COUNT => RatingSentiment::VeryNegative()->description,
            default => RatingSentiment::Negative()->description,
        };
    }
}
