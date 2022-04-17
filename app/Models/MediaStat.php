<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaStat extends KModel
{
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
