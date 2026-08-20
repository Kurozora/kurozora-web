<?php

namespace App\Models;

use App\Enums\ReviewRecommendation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaRatingRevision extends KModel
{
    // The number of superseded versions kept per review
    const int MAXIMUM_KEPT = 20;

    // How long a version must stand before it is worth keeping
    const int MINIMUM_STANDING_MINUTES = 5;

    // Table name
    const string TABLE_NAME = 'media_rating_revisions';
    protected $table = self::TABLE_NAME;

    /**
     * Whether the model should be timestamped.
     *
     * @var bool $timestamps
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_spoiler' => 'boolean',
            'recommendation' => ReviewRecommendation::class,
            'written_at' => 'datetime',
        ];
    }

    /**
     * Returns the review the revision belongs to.
     *
     * @return BelongsTo
     */
    public function mediaRating(): BelongsTo
    {
        return $this->belongsTo(MediaRating::class, 'rating_id');
    }
}
