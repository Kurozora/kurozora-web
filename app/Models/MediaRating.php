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

    // Rating boundaries
    const float MIN_RATING_VALUE = 0.00;
    const float MAX_RATING_VALUE = 5.00;

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
     * Calculate and persist the overall rating from category scores.
     * Should be called after saving all RatingCategoryScore rows.
     *
     * @return $this
     */
    public function recalculateFromCategoryScores(): static
    {
        $scores = $this->categoryScores()->pluck('score');

        if ($scores->isEmpty()) {
            return $this;
        }

        $this->rating = round($scores->average(), 1);
        $this->save();

        return $this;
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
