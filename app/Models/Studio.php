<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Studio extends KModel
{
    use HasFactory;

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
        return $this->belongsToMany(Anime::class)
            ->withTimestamps();
    }

    /**
     * Retrieves the anime for a Studio item in an array
     *
     * @param int $limit
     * @param int $page
     * @param array $where
     * @return mixed
     */
    public function getAnime(int $limit = 25, int $page = 1, array $where = []): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'studios.anime', 'id' => $this->id, 'limit' => $limit, 'page' => $page, 'where' => $where]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SECONDS, function () use ($limit, $where) {
            return $this->anime()->where($where)->paginate($limit);
        });
    }
}
