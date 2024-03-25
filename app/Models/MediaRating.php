<?php

namespace App\Models;

use App\Traits\Model\MorphTvRated;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaRating extends KModel
{
    use HasUlids,
        MorphTvRated,
        SoftDeletes;

    // Rating boundaries
    const float MIN_RATING_VALUE = 0.00;
    const float MAX_RATING_VALUE = 5.00;

    // Table name
    const string TABLE_NAME = 'media_ratings';
    protected $table = self::TABLE_NAME;

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
}
