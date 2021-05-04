<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaRelation extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'media_relations';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the parent anime in the relationship.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class, 'media_id');
    }

    /**
     * Returns the anime related to the parent anime.
     *
     * @return BelongsTo
     */
    public function related_anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class, 'related_id');
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
