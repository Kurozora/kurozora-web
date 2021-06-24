<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Person extends KModel
{
    use HasFactory;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_ANIME_SECONDS = 120 * 60;
    const CACHE_KEY_CHARACTERS_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'people';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'alternative_names' => AsArrayObject::class,
        'birth_date'        => 'date',
        'website_urls'      => AsArrayObject::class,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_name',
        'full_given_name',
    ];

    /**
     * Returns the full name of the person.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $lastNameEmpty = empty($this->last_name);
        $firstNameEmpty = empty($this->first_name);

        if ($lastNameEmpty && !$firstNameEmpty) {
            return $this->first_name;
        } else if ($firstNameEmpty && !$lastNameEmpty) {
            return $this->last_name;
        } else if ($firstNameEmpty && $lastNameEmpty) {
            return '';
        }

        return $this->last_name . ', ' . $this->first_name;
    }

    /**
     * Returns the full given name of the person.
     *
     * @return string
     */
    public function getFullGivenNameAttribute(): string
    {
        $familyNameEmpty = empty($this->family_name);
        $givenNameEmpty = empty($this->given_name);

        if ($familyNameEmpty && !$givenNameEmpty) {
            return $this->given_name;
        } else if ($givenNameEmpty && !$familyNameEmpty) {
            return $this->family_name;
        } else if ($givenNameEmpty && $familyNameEmpty) {
            return '';
        }

        return $this->family_name . ', ' . $this->given_name;
    }

    /**
     * Returns the anime the character belongs to.
     *
     * @return BelongsToMany
     */
    function anime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, AnimeCast::class)->distinct();
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
        $cacheKey = self::cacheKey(['name' => 'person.anime', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SECONDS, function () use ($limit) {
            return $this->anime()->limit($limit)->get();
        });
    }

    /**
     * Returns the characters the person belongs to.
     *
     * @return BelongsToMany
     */
    function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, AnimeCast::class)->distinct();
    }

    /**
     * Retrieves the characters for a person item in an array.
     *
     * @param int|null $limit
     * @return Collection
     */
    public function getCharacters(int $limit = null): Collection
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'person.characters', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->characters()->limit($limit)->get();
        });
    }

    /**
     * Returns the cast relationship the character has.
     *
     * @return HasMany
     */
    public function cast(): HasMany
    {
        return $this->hasMany(AnimeCast::class, 'person_id');
    }
}
