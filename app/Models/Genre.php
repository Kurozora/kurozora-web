<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends KModel
{
    // Table name
    const TABLE_NAME = 'genres';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the Anime with the genre
     *
     * @return BelongsToMany
     */
    function animes(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, AnimeGenre::TABLE_NAME, 'genre_id', 'anime_id');
    }
}
