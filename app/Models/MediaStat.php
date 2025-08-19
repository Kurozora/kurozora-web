<?php

namespace App\Models;

use App\Enums\RatingSentiment;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaStat extends KModel
{
    use SoftDeletes;

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
     * The rating with the highest percentage.
     *
     * @return float|int
     */
    public function getHighestRatingPercentageAttribute(): float|int
    {
        $ratingTotal = max($this->rating_count, 1);
        $starRanges = [
            '1' => $this->rating_1 + $this->rating_2,
            '2' => $this->rating_3 + $this->rating_4,
            '3' => $this->rating_5 + $this->rating_6,
            '4' => $this->rating_7 + $this->rating_8,
            '5' => $this->rating_9 + $this->rating_10,
        ];

        $maxRating = (int) max($starRanges);

        return 100 / $ratingTotal * $maxRating;
    }

    /**
     * The sentiment label.
     *
     * @return string
     */
    public function getSentimentAttribute(): string
    {
        $ratingCounts = [
            '1' => $this->rating_1 + $this->rating_2,
            '2' => $this->rating_3 + $this->rating_4,
            '3' => $this->rating_5 + $this->rating_6,
            '4' => $this->rating_7 + $this->rating_8,
            '5' => $this->rating_9 + $this->rating_10
        ];

        $totalRatings = array_sum($ratingCounts);

        // Check if there are no ratings yet
        if ($totalRatings === 0) {
            return RatingSentiment::NotEnough()->description;
        }

        $positiveThreshold = $totalRatings * 0.75;
        $negativeThreshold = $totalRatings * 0.25;

        if ($ratingCounts['5'] >= $positiveThreshold) {
            return RatingSentiment::OverwhelminglyPositive()->description;
        } elseif ($ratingCounts['1'] >= $negativeThreshold) {
            return RatingSentiment::OverwhelminglyNegative()->description;
        } elseif ($ratingCounts['1'] + $ratingCounts['2'] >= $negativeThreshold) {
            return RatingSentiment::MixedFeelings()->description;
        } elseif ($this->rating_average <= 2.5) {
            return RatingSentiment::Negative()->description;
        } elseif ($this->rating_average <= 3.5) {
            return RatingSentiment::Average()->description;
        } else {
            return RatingSentiment::Positive()->description;
        }
    }
}
