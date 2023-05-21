<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class MediaStat extends KModel
{
    use Searchable,
        SoftDeletes;

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
}
