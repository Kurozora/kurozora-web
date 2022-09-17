<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaStat extends KModel
{
    use SoftDeletes;

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
