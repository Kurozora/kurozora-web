<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Studio extends KModel
{
    // How long to cache certain responses
    const CACHE_KEY_ANIME_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'studios';
    protected $table = self::TABLE_NAME;

    /**
     * Casts rules.
     *
     * @var array
     */
    protected $casts = [
        'founded' => 'date',
    ];

    /**
     * Returns the anime that belongs to the studio
     *
     * @return HasMany
     */
    public function anime_studio(): HasMany
    {
        return $this->hasMany(AnimeStudio::class);
    }

    /**
     * Returns the anime that belongs to the studio
     *
     * @return BelongsToMany
     */
    public function anime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class);
    }

    /**
     * Retrieves the anime for a Studio item in an array
     *
     * @param array $where
     * @return Collection
     */
    public function getAnime($where = []): Collection
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'studios', 'id' => $this->id, 'where' => $where]);

        // Retrieve or save cached result
        $animeInfo = Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SECONDS, function () use ($where) {
            return $this->anime()->where($where)->get();
        });

        return $animeInfo;
    }
}
