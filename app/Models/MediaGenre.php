<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaGenre extends KModel
{
    // Table name
    const TABLE_NAME = 'media_genres';
    protected $table = self::TABLE_NAME;

    /**
     * The anime belonging to the media genre.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class, 'media_id');
    }

    /**
     * The genre belonging to the media genre.
     *
     * @return BelongsTo
     */
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }
}
