<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SongLyric extends KModel
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'song_lyrics';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'agents' => 'array',
            'leading_silence_ms' => 'integer',
            'lyric_offset_ms' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    /**
     * The song the lyrics belong to.
     *
     * @return BelongsTo
     */
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    /**
     * The user who submitted the lyrics.
     *
     * @return BelongsTo
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * The lines that make up the lyrics, across all tracks.
     *
     * @return HasMany
     */
    public function lines(): HasMany
    {
        return $this->hasMany(SongLyricLine::class)
            ->orderBy('position');
    }
}
