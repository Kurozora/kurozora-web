<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeStudio extends Model
{
    // Table name
    const TABLE_NAME = 'anime_studio';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the anime belonging to the studio.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function anime() {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Returns the studio belonging to the anime.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function studio() {
        return $this->belongsTo(Studio::class);
    }
}
