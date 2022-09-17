<?php

namespace App\Models;

use App\Enums\SongType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class AnimeSong extends KModel implements Sitemapable
{
    use HasFactory,
        SoftDeletes;

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
     * Get the song type attribute.
     *
     * @param int|null $value
     * @return SongType|null
     */
    public function getTypeAttribute(?int $value): ?SongType
    {
        return isset($value) ? SongType::fromValue($value) : null;
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('anime.songs', $this->anime))
            ->setChangeFrequency('monthly');
    }
}
