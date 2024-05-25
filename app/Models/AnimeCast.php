<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class AnimeCast extends KModel implements Sitemapable
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'anime_casts';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the anime relationship of the cast.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
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
     * Returns the person relationship of the cast.
     *
     * @return BelongsTo
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
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
     * Returns the language relationship of the cast.
     *
     * @return BelongsTo
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('anime.cast', $this->anime))
            ->setChangeFrequency('weekly')
            ->setLastModificationDate($this->anime->updated_at);
    }
}
