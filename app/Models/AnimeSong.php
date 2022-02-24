<?php

namespace App\Models;

use App\Enums\SongType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class AnimeSong extends KModel implements Sitemapable
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
//                        ->withoutGlobalScope(new TvRatingScope());
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

    /**
     * Get the type attribute.
     *
     * @param $value
     * @return SongType
     */
    public function getTypeAttribute($value): SongType
    {
        return SongType::fromValue($value);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return route('anime.songs', $this->anime);
    }
}
