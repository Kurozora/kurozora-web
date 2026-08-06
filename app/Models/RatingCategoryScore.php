<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingCategoryScore extends KModel
{
    // Score boundaries
    const float MIN_SCORE_VALUE = 0.00;
    const float MAX_SCORE_VALUE = 10.00;

    // Table name
    const string TABLE_NAME = 'rating_category_scores';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'score' => 'double',
        ];
    }

    /**
     * Returns the media rating the score belongs to.
     *
     * @return BelongsTo
     */
    public function mediaRating(): BelongsTo
    {
        return $this->belongsTo(MediaRating::class, 'rating_id');
    }

    /**
     * Returns the rating category being scored.
     *
     * @return BelongsTo
     */
    public function ratingCategory(): BelongsTo
    {
        return $this->belongsTo(RatingCategory::class);
    }
}
