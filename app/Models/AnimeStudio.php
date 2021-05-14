<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimeStudio extends KModel
{
    // Table name
    const TABLE_NAME = 'anime_studio';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the anime belonging to the studio.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Returns the studio belonging to the anime.
     *
     * @return BelongsTo
     */
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }
}
