<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SongLyricLine extends KModel
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'song_lyric_lines';
    protected $table = self::TABLE_NAME;

    /**
     * The lyrics the line belongs to.
     *
     * @return BelongsTo
     */
    public function lyric(): BelongsTo
    {
        return $this->belongsTo(SongLyric::class, 'song_lyric_id');
    }

    /**
     * The words that make up the line, when word-timed.
     *
     * @return HasMany
     */
    public function words(): HasMany
    {
        return $this->hasMany(SongLyricWord::class)
            ->orderBy('position');
    }
}
