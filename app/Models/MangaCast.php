<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class MangaCast extends KModel implements Sitemapable
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'manga_casts';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the manga relationship of the cast.
     *
     * @return BelongsTo
     */
    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
    }

    /**
     * Returns the character relationship of the cast.
     *
     * @return BelongsTo
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Returns the role relationship of the cast.
     *
     * @return BelongsTo
     */
    public function castRole(): BelongsTo
    {
        return $this->belongsTo(CastRole::class);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('manga.cast', $this->manga))
            ->setChangeFrequency('weekly');
    }
}
