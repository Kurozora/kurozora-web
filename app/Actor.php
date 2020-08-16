<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

class Actor extends KModel
{
    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_ANIME_SECONDS = 120 * 60;
    const CACHE_KEY_CHARACTERS_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'actors';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the full name of the actor.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->last_name . ', ' . $this->first_name;
    }

    /**
     * Returns the anime the character belongs to.
     *
     * @return HasManyDeep
     */
    function anime(): HasManyDeep
    {
        return $this->hasManyDeep(Anime::class, [ActorCharacter::class, ActorCharacterAnime::class], ['actor_id', 'actor_character_id', 'id'], ['id', 'id', 'anime_id'])->distinct();
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
        $cacheKey = self::cacheKey(['name' => 'actor.anime', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SECONDS, function () use ($limit) {
            return $this->anime()->limit($limit)->get();
        });
    }

    /**
     * Returns the characters the actor belongs to.
     *
     * @return BelongsToMany
     */
    function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class);
    }

    /**
     * Retrieves the characters for an actor item in an array.
     *
     * @param int|null $limit
     * @return Collection
     */
    public function getCharacters(int $limit = null): Collection
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'actor.characters', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->characters()->limit($limit)->get();
        });
    }
}
