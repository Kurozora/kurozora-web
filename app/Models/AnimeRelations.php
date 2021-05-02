<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimeRelations extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'anime_relations';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the parent anime in the relationship.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Returns the anime related to the parent anime.
     *
     * @return BelongsTo
     */
    public function related_anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class, 'related_anime_id', 'id');
    }
}
