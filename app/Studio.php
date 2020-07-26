<?php

namespace App;

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function anime_studio()
    {
        return $this->hasMany(AnimeStudio::class);
    }

    /**
     * Returns the anime that belongs to the studio
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function anime()
    {
        return $this->hasManyThrough(Anime::class, AnimeStudio::class, 'studio_id', 'id', 'id', 'anime_id');
    }

    /**
     * Retrieves the anime for a Studio item in an array
     *
     * @return array
     */
    public function getAnime() {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'studios', 'id' => $this->id]);

        // Retrieve or save cached result
        $animeInfo = Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SECONDS, function () {
            return $this->anime()->get();
        });

        return $animeInfo;
    }
}
