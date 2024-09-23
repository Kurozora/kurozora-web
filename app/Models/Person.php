<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\AstrologicalSign;
use App\Enums\MediaCollection;
use App\Scopes\BornTodayScope;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasMediaRatings;
use App\Traits\Model\HasMediaStat;
use App\Traits\Model\HasSlug;
use App\Traits\Model\HasViews;
use App\Traits\SearchFilterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\SlugOptions;

class Person extends KModel implements HasMedia, Sitemapable
{
    use HasFactory,
        HasMediaStat,
        HasSlug,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Searchable,
        SearchFilterable,
        SoftDeletes;
    use HasMediaRatings {
        mediaRatings as protected parentMediaRatings;
    }

    // Maximum relationships fetch limit
    const int MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // Table name
    const string TABLE_NAME = 'people';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'alternative_names' => AsArrayObject::class,
            'birthdate'         => 'date',
            'deceased_date'     => 'date',
            'website_urls'      => AsArrayObject::class,
        ];
    }

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
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('slug_name')
            ->saveSlugsTo('slug');
    }

    /**
     * The orderable properties.
     *
     * @return array[]
     */
    public static function webSearchOrders(): array
    {
        $order = [
            'full_name' => [
                'title' => __('Name'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'birthdate' => [
                'title' => __('Birthday'),
                'options' => [
                    'Default' => null,
                    'Youngest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'deceased_date' => [
                'title' => __('Deceased Date'),
                'options' => [
                    'Default' => null,
                    'Recent' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'astrological_sign' => [
                'title' => __('Astrological Sign'),
                'options' => [
                    'Default' => null,
                    'Aries-Pisces' => 'asc',
                    'Pisces-Aries' => 'desc',
                ],
                'selected' => null,
            ],
        ];

        return $order;
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
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param Builder $query
     * @return Builder
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->withoutGlobalScopes();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $person = $this->toArray();
        $person['letter'] = str_index($this->full_name);
        $person['full_name'] = $this->full_name;
        $person['full_given_name'] = $this->full_given_name;
        $person['birthdate'] = $this->birthdate?->timestamp;
        $person['deceased_date'] = $this->deceased_date?->timestamp;
        $person['created_at'] = $this->created_at?->timestamp;
        $person['updated_at'] = $this->updated_at?->timestamp;
        return $person;
    }

    /**
     * Returns the slug name of the person.
     *
     * @return string
     */
    public function getSlugNameAttribute(): string
    {
        $fullName = array_filter([
            $this->first_name,
            $this->last_name
        ]);

        return implode(', ', $fullName);
    }

    /**
     * Returns the full name of the person.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $fullName = array_filter([
            $this->last_name,
            $this->first_name
        ]);

        return implode(', ', $fullName);
    }

    /**
     * Returns the full given name of the person.
     *
     * @return string
     */
    public function getFullGivenNameAttribute(): string
    {
        $fullGivenName = array_filter([
            $this->family_name,
            $this->given_name
        ]);

        return implode(', ', $fullGivenName);
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
            $age = (int) $birthdate->diffInYears($this->deceased_date);
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
     * Get the model's ratings.
     *
     * @return MorphMany
     */
    public function mediaRatings(): MorphMany
    {
        return $this->parentMediaRatings()
            ->withoutGlobalScopes();
    }

    /**
     * Returns the anime the person belongs to.
     *
     * @return HasManyThrough
     */
    function anime(): HasManyThrough
    {
        /** @var Builder $union */ // To silence IDE warning for not matching type
        $union = $this->hasManyThrough(Anime::class, MediaStaff::class, 'person_id', 'id', 'id', 'model_id')
            ->where('model_type', '=', Anime::class)
            ->select(Anime::TABLE_NAME . '.*', MediaStaff::TABLE_NAME . '.person_id as laravel_through_key');
        // Second select above isn't used, but SQL will
        // complain if number of selected columns in a union
        // don't match. So since hasManyThrough for a non-morphic
        // relation uses a similar select structure, we fake
        // the number of columns in the morphic one using
        // an unnecessary select. It should have no performance
        // penalties, but it would be nice to improve this.

        return $this->hasManyThrough(Anime::class, AnimeCast::class, 'person_id', 'id', 'id', 'anime_id')
            ->union($union);
    }

    /**
     * Returns the manga the person belongs to.
     *
     * @return HasManyThrough
     */
    function manga(): HasManyThrough
    {
        return $this->hasManyThrough(Manga::class, MediaStaff::class, 'person_id', 'id', 'id', 'model_id')
            ->where('model_type', '=', Manga::class);
    }

    /**
     * Returns the game the person belongs to.
     *
     * @return HasManyThrough
     */
    function games(): HasManyThrough
    {
        /** @var Builder $union */ // To silence IDE warning for not matching type
        $union = $this->hasManyThrough(Game::class, MediaStaff::class, 'person_id', 'id', 'id', 'model_id')
            ->where('model_type', '=', Game::class)
            ->select(Game::TABLE_NAME . '.*', MediaStaff::TABLE_NAME . '.person_id as laravel_through_key');
        // Second select above isn't used, but SQL will
        // complain if number of selected columns in a union
        // don't match. So since hasManyThrough for a non-morphic
        // relation uses a similar select structure, we fake
        // the number of columns in the morphic one using
        // an unnecessary select. It should have no performance
        // penalties, but it would be nice to improve this.

        return $this->hasManyThrough(Game::class, GameCast::class, 'person_id', 'id', 'id', 'game_id')
            ->union($union);
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
     * Returns the cast relationship the character has.
     *
     * @return HasMany
     */
    public function animeCast(): HasMany
    {
        return $this->hasMany(AnimeCast::class, 'person_id');
    }

    /**
     * Returns the cast relationship the character has.
     *
     * @return HasMany
     */
    public function gameCast(): HasMany
    {
        return $this->hasMany(GameCast::class, 'person_id');
    }

    /**
     * Returns the cast relationship the character has.
     *
     * @return HasMany
     */
    public function mediaStaff(): HasMany
    {
        return $this->hasMany(MediaStaff::class, 'person_id');
    }

    /**
     * Eloquent builder scope that limits the query to the characters born today.
     *
     * @param Builder $query
     * @param int $limit
     */
    public function scopeBornToday(Builder $query, int $limit = 10): void
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
            ->setChangeFrequency('weekly')
            ->setLastModificationDate($this->updated_at);
    }
}
