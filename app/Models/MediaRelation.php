<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaRelation extends KModel
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'media_relations';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the parent anime in the relationship.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the anime related to the parent anime.
     *
     * @return MorphTo
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the relation between the media.
     *
     * @return BelongsTo
     */
    public function relation(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'relation_id');
    }
}
