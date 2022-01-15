<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AnimeStudio extends Pivot
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'anime_studio';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_licensor'   => 'boolean',
        'is_producer'   => 'boolean',
        'is_studio'     => 'boolean',
    ];

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
        return $this->belongsTo(Studio::class)->where('type', 'anime');
    }
}
