<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\AstrologicalSign;
use App\Scopes\BornTodayScope;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasProfileImage;
use App\Traits\Model\HasTranslatableSlug;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;
use Request;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\SlugOptions;

class Character extends KModel implements HasMedia, Sitemapable
{
    use HasFactory,
        HasProfileImage,
        HasTranslatableSlug,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Searchable,
        Translatable;

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
//        'age_string',
//        'astrological_sign_string',
//        'birthdate',
//        'height_string',
//        'profile_image',
//        'profile_image_url',
//        'weight_string',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        if (Request::wantsJson()) {
            return parent::getRouteKeyName();
        }
        return 'slug';
    }

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->profileImageCollectionName)
            ->singleFile();
    }

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return 'characters_index';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'mal_id' => $this->mal_id,
            'slug' => $this->slug,
            'nicknames' => $this->nicknames,
            'translations' => $this->translations
        ];
    }

    /**
     * The age string of the character.
     *
     * @return string|null
     */
    public function getAgeStringAttribute(): ?string
    {
        $age = $this->age;

        if (empty($age)) {
            return null;
        }

        $shortNumber = number_shorten($age, 0);
        return trans_choice('{1} :x year old|[2,*] :x years old', $age, ['x' => $shortNumber]);
    }

    /**
     * The height string of the character.
     *
     * @return string|null
     */
    public function getHeightStringAttribute(): ?string
    {
        $height = $this->height;

        if (empty($height)) {
            return null;
        }

        // Remove decimals if unnecessary.
        $height += 0;

        // Use cm if shorter than a kilometer.
        if ($height < 1000) {
            return __(':x cm', ['x' => $height]);
        }

        // Otherwise, convert to km for clarity.
        $shortNumber = number_shorten($height / 1000, 2);
        return __(':x km', ['x' => $shortNumber]);
    }

    /**
     * The weight string of the character.
     *
     * @return string|null
     */
    public function getWeightStringAttribute(): ?string
    {
        $weight = $this->weight;

        if (empty($weight)) {
            return null;
        }

        // Remove decimals if unnecessary.
        $weight += 0;

        // Use grams if less than a kilogram.
        if ($weight < 1000) {
            return trans_choice('{1} :x gram|[2,*] :x grams', $weight, ['x' => $weight]);
        }

        // Otherwise, use kg for clarity.
        $shortNumber = number_shorten($weight / 1000, 2);
        return __(':x kg', ['x' => $shortNumber]);
    }

    /**
     * The birthdate of the character.
     *
     * @return string|null
     */
    public function getBirthdateAttribute(): ?string
    {
        $birthdate = now();
        $format = '';

        if (!empty($this->birth_month)) {
            $birthdate->month($this->birth_month);
            $format .= 'F ';
        }
        if (!empty($this->birth_day)) {
            $birthdate->day($this->birth_day);
            $format .= 'jS';
        }

        return $format ? $birthdate->format($format) : null;
    }

    /**
     * The astrological sign of the character.
     *
     * @return string|null
     */
    public function getAstrologicalSignStringAttribute(): ?string
    {
        return AstrologicalSign::getDescription($this->astrological_sign) ?: null;
    }

    /**
     * Returns the people the character belongs to.
     *
     * @return BelongsToMany
     */
    function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, AnimeCast::class)
            ->distinct();
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
        return $this->belongsToMany(Anime::class, AnimeCast::class)
            ->distinct();
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

    /**
     * Eloquent builder scope that limits the query to the characters born today.
     *
     * @param Builder $query
     * @param int $limit
     */
    public function scopeBornToday(Builder $query, int $limit = 10)
    {
        $bornToday = new BornTodayScope();
        $bornToday->apply($query->limit($limit), $this);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('characters.details', $this))
            ->setChangeFrequency('weekly');
    }
}
