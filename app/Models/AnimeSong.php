<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimeSong extends Model
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'anime_songs';
    protected $table = self::TABLE_NAME;

    /**
     * The anime relationship of anime song.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    /**
     * The song relationship of anime song.
     *
     * @return BelongsTo
     */
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
