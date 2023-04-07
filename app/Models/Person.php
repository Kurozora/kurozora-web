<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\AstrologicalSign;
use App\Enums\MediaCollection;
use App\Scopes\BornTodayScope;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasViews;
use App\Traits\SearchFilterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;
use Request;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Person extends KModel implements HasMedia, Sitemapable
{
    use HasFactory,
        HasSlug,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Searchable,
        SearchFilterable,
        SoftDeletes;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_ANIME_SECONDS = 120 * 60;
    const CACHE_KEY_MANGA_SECONDS = 120 * 60;
    const CACHE_KEY_GAMES_SECONDS = 120 * 60;
    const CACHE_KEY_CHARACTERS_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'people';
    protected $table = self::TABLE_NAME;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'media',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'alternative_names' => AsArrayObject::class,
        'birthdate'         => 'date',
        'deceased_date'     => 'date',
        'website_urls'      => AsArrayObject::class,
    ];

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::Profile)
            ->singleFile();
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
//        'age_string',
//        'full_name',
//        'full_given_name',
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
            ->generateSlugsFrom('full_name')
            ->saveSlugsTo('slug');
    }

    /**
     * The filterable properties.
     *
     * @return array[]
     */
    public static function webSearchFilters(): array
    {
        $filter = [
            'birthdate' => [
                'title' => __('Birthday'),
                'type' => 'date',
                'selected' => null,
            ],
            'deceased_date' => [
                'title' => __('Deceased Date'),
                'type' => 'date',
                'selected' => null,
            ],
            'astrological_sign' => [
                'title' => __('Astrological Sign'),
                'type' => 'select',
                'options' => AstrologicalSign::asSelectArray(),
                'selected' => null,
            ],
        ];

        return $filter;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $person = $this->toArray();
        $person['full_name'] = $this->full_name;
        $person['full_given_name'] = $this->full_given_name;
        $person['birthdate'] = $this->birthdate?->timestamp;
        $person['deceased_date'] = $this->deceased_date?->timestamp;
        $person['created_at'] = $this->created_at?->timestamp;
        $person['updated_at'] = $this->updated_at?->timestamp;
        return $person;
    }

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
     * The age string of the person.
     *
     * @return string|null
     */
    public function getAgeStringAttribute(): ?string
    {
        $birthdate = $this->birthdate;

        if (empty($birthdate)) {
            return null;
        }

        if (empty($this->deceased_date)) {
            $age = $birthdate->age;
        } else {
            $age = $birthdate->diffInYears($this->deceased_date);
        }

        return trans_choice('{1} :x year old|[2,*] :x years old', $age, ['x' => $age]);
    }

    /**
     * The astrological sign of the character.
     *
     * @param int|null $value
     * @return AstrologicalSign|null
     */
    public function getAstrologicalSignAttribute(?int $value): ?AstrologicalSign
    {
        return isset($value) ? AstrologicalSign::fromValue($value) : null;
    }

    /**
     * Returns the anime the person belongs to.
     *
     * @return BelongsToMany
     */
    function anime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, AnimeCast::class)
            ->distinct();
    }

    /**
     * Retrieves the anime for a person item in an array.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getAnime(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'person.anime', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SECONDS, function () use ($limit) {
            return $this->anime()->paginate($limit);
        });
    }

    /**
     * Returns the manga the person belongs to.
     *
     * @return BelongsToMany
     */
    function manga(): BelongsToMany
    {
        return $this->belongsToMany(Manga::class, MediaStaff::class, 'person_id', 'model_id')
            ->distinct();
    }

    /**
     * Retrieves the manga for a person item in an array.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getManga(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'person.manga', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_MANGA_SECONDS, function () use ($limit) {
            return $this->manga()->paginate($limit);
        });
    }

    /**
     * Returns the game the person belongs to.
     *
     * @return BelongsToMany
     */
    function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, GameCast::class)
            ->distinct();
    }

    /**
     * Retrieves the game for a person item in an array.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getGames(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'person.games', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_GAMES_SECONDS, function () use ($limit) {
            return $this->games()->paginate($limit);
        });
    }

    /**
     * Returns the characters the person belongs to.
     *
     * @return BelongsToMany
     */
    function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, AnimeCast::class)
            ->distinct();
    }

    /**
     * Retrieves the characters for a person item in an array.
     *
     * @param int $limit
     * @param int $page
     * @return Collection
     */
    public function getCharacters(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'person.characters', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->characters()->paginate($limit);
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
        return Url::create(route('people.details', $this))
            ->setChangeFrequency('weekly');
    }
}
