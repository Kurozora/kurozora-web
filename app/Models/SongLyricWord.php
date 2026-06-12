<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SongLyricWord extends KModel
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'song_lyric_words';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_background' => 'boolean',
            'trailing_space' => 'boolean',
        ];
    }

    /**
     * The line the word belongs to.
     *
     * @return BelongsTo
     */
    public function line(): BelongsTo
    {
        return $this->belongsTo(SongLyricLine::class, 'song_lyric_line_id');
    }
}
