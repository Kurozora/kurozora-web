<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Cache;

class Song extends KModel
{
    use HasFactory;

    // How long to cache certain responses
    const CACHE_KEY_ANIMES_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'songs';
    protected $table = self::TABLE_NAME;

    /**
     * Get the anime-songs relationship.
     *
     * @return HasMany
     */
    public function anime_songs(): HasMany
    {
        return $this->hasMany(AnimeSong::class);
    }

    /**
     * Get the anime-songs relationship.
     *
     * @return HasManyThrough
     */
    public function anime(): HasManyThrough
    {
        return $this->hasManyThrough(Anime::class, AnimeSong::class, 'song_id', 'id', 'id', 'anime_id');
    }

    /**
     * Returns the anime relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getAnime(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'song.anime', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIMES_SECONDS, function () use ($limit) {
            return $this->anime()->paginate($limit);
        });
    }
}
