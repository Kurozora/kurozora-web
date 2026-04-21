<?php

namespace App\Models;

use App\Enums\RatingReactionType;
use App\Enums\RatingStyle;
use App\Scopes\TvRatingScope;
use App\Traits\Model\MorphTvRated;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaRating extends KModel
{
    use MorphTvRated,
        SoftDeletes;

// Rating boundaries (internal storage is 0-10)
    const float MIN_RATING_VALUE = 0.00;
    const float MAX_RATING_VALUE = 10.00;

    // Legacy max for backwards compatibility display
    const float LEGACY_MAX_RATING_VALUE = 5.00;

    // Table name
    const string TABLE_NAME = 'media_ratings';
    protected $table = self::TABLE_NAME;

    protected $casts = [
        'rating_style' => RatingStyle::class,
        'helpful_count'    => 'integer',
        'not_helpful_count' => 'integer',
    ];

    /**
     * Returns the model related to the media rating.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the model related to the media rating.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Per-category scores (only populated for Detailed ratings).
     *
     * @return HasMany
     */
    public function categoryScores(): HasMany
    {
        return $this->hasMany(RatingCategoryScore::class);
    }

    /**
     * Get the rating value formatted for display based on rating style.
     *
     * @return float
     */
    public function getDisplayRatingAttribute(): float
    {
        // For standard style (5-star), convert internal 0-10 to 0-5
        if ($this->rating_style === null || $this->rating_style->value === RatingStyle::Standard) {
            return RatingStyle::internalToStandard($this->rating);
        }

        // For all other styles, return the 0-10 rating as-is
        return $this->rating;
    }

    /**
     * Get the maximum rating value for display based on rating style.
     *
     * @return float
     */
    public function getDisplayMaxRatingAttribute(): float
    {
        if ($this->rating_style === null || $this->rating_style->value === RatingStyle::Standard) {
            return self::LEGACY_MAX_RATING_VALUE;
        }

        return self::MAX_RATING_VALUE;
    }

    /**
     * Calculate overall rating from category scores.
     *
     * @return float|null
     */
    public function calculateOverallFromCategories(): ?float
    {
        $scores = $this->categoryScores()->with('ratingCategory')->get();

        if ($scores->isEmpty()) {
            return null;
        }

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($scores as $score) {
            $weight = $score->ratingCategory->weight ?? 1.0;
            $weightedSum += $score->score * $weight;
            $totalWeight += $weight;
        }

        if ($totalWeight === 0) {
            return null;
        }

        return $weightedSum / $totalWeight;
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(RatingReaction::class);
    }

    public function syncReactionCounts(): void
    {
        $this->helpful_count = $this->reactions()
            ->where('type', RatingReactionType::Helpful)
            ->count();

        $this->not_helpful_count = $this->reactions()
            ->where('type', RatingReactionType::NotHelpful)
            ->count();

        $this->saveQuietly();
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  Model|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Builder
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): Builder
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->withoutGlobalScopes([TvRatingScope::class]);
    }
}
