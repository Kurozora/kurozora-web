<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingCategoryScore extends KModel
{
    // Table name
    const string TABLE_NAME = 'rating_category_scores';
    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'rating_id',
        'rating_category_id',
        'score',
        'review',
    ];

    protected $casts = [
        'score' => 'double',
    ];

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    /**
     * The parent rating this score belongs to.
     *
     * @return BelongsTo
     */
    public function rating(): BelongsTo
    {
        return $this->belongsTo(MediaRating::class);
    }

    /**
     * The category being scored.
     *
     * @return BelongsTo
     */
    public function ratingCategory(): BelongsTo
    {
        return $this->belongsTo(RatingCategory::class);
    }
}