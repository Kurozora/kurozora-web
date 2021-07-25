<?php

namespace App\Models;

use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasProfileImage;
use App\Traits\Searchable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Character extends KModel implements HasMedia
{
    use HasFactory,
        HasProfileImage,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Searchable,
        Translatable;

    // Maximum amount of returned search results
    const MAX_SEARCH_RESULTS = 10;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_PEOPLE_SECONDS = 120 * 60;
    const CACHE_KEY_ANIME_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'characters';
    protected $table = self::TABLE_NAME;

    /**
     * Translatable attributes.
     *
     * @var array
     */
    public array $translatedAttributes = [
        'name',
        'about',
    ];

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'name' => 10,
            'about' => 5,
        ],
        'joins' => [
            'character_translations' => [
                'characters.id',
                'character_translations.character_id'
            ],
        ],
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'nicknames' => AsArrayObject::class,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_image',
        'profile_image_url',
    ];

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->profileImageCollectionName)
            ->singleFile();
    }

    /**
     * Returns the people the character belongs to.
     *
     * @return BelongsToMany
     */
    function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, AnimeCast::class)->distinct();
    }

    /**
     * Retrieves the people for a character item in an array.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getPeople(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'character.people', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_PEOPLE_SECONDS, function () use ($limit) {
            return $this->people()->paginate($limit);
        });
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
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getAnime(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'character.anime', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SECONDS, function () use ($limit) {
            return $this->anime()->paginate($limit);
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

    /**
     * The character's translation relationship.
     *
     * @return HasMany
     */
    public function character_translations(): HasMany
    {
        return $this->hasMany(CharacterTranslation::class);
    }
}
