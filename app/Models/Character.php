<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

class Character extends KModel
{
    use HasFactory;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_ACTORS_SECONDS = 120 * 60;
    const CACHE_KEY_ANIME_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'characters';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'nicknames' => AsArrayObject::class,
    ];

    /**
     * Returns the actors the character belongs to.
     *
     * @return HasManyDeep
     */
    function actors(): HasManyDeep
    {
        return $this->hasManyDeep(Actor::class, [AnimeCast::class], ['character_id', 'id'], ['id', 'actor_id'])->distinct();
    }

    /**
     * Retrieves the actors for a character item in an array.
     *
     * @param int|null $limit
     * @return Collection
     */
    public function getActors(int $limit = null): Collection
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'character.actors', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ACTORS_SECONDS, function () use ($limit) {
            return $this->actors()->limit($limit)->get();
        });
    }

    /**
     * Returns the anime the character belongs to.
     *
     * @return HasManyDeep
     */
    function anime(): HasManyDeep
    {
        return $this->hasManyDeep(Anime::class, [AnimeCast::class], ['character_id', 'id'], ['id', 'anime_id'])->distinct();
    }

    /**
     * Retrieves the anime for a character item in an array.
     *
     * @param int|null $limit
     * @return Collection
     */
    public function getAnime(int $limit = null): Collection
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'character.anime', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SECONDS, function () use ($limit) {
            return $this->anime()->limit($limit)->get();
        });
    }

    /**
     * Returns the cast relationship the character has.
     *
     * @return HasMany
     */
    public function cast(): HasMany
    {
        return $this->hasMany(AnimeCast::class);
    }
}
