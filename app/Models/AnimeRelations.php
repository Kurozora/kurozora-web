<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnimeRelations extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'anime_relations';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the parent anime in the relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function anime() {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Returns the anime related to the parent anime.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function related_anime() {
        return $this->belongsTo(Anime::class, 'related_anime_id', 'id');
    }
}
