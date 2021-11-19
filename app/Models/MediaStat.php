<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaStat extends KModel
{
    // Rating boundaries
    const MIN_RATING_VALUE = 0.00;
    const MAX_RATING_VALUE = 5.00;

    // Table name
    const TABLE_NAME = 'media_stats';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the model in the category item.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}

