<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\AstrologicalSign;
use App\Enums\CharacterStatus;
use App\Enums\MediaCollection;
use App\Scopes\BornTodayScope;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasMediaRatings;
use App\Traits\Model\HasMediaStat;
use App\Traits\Model\HasTranslatableSlug;
use App\Traits\Model\HasViews;
use App\Traits\SearchFilterable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\SlugOptions;

class Character extends KModel implements HasMedia, Sitemapable
{
    use HasFactory,
        HasMediaStat,
        HasTranslatableSlug,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Searchable,
        SearchFilterable,
        SoftDeletes,
        Translatable;
    use HasMediaRatings {
        mediaRatings as protected parentMediaRatings;
    }

    // Maximum relationships fetch limit
    const int MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // Table name
    const string TABLE_NAME = 'characters';
    protected $table = self::TABLE_NAME;

    /**
     * Translatable attributes.
     *
     * @var array
     */
    public array $translatedAttributes = [
        'name',
        'about',
        'short_description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'nicknames' => AsArrayObject::class,
        ];
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
        $this->addMediaCollection(MediaCollection::Profile)
            ->singleFile();
    }

    /**
     * The orderable properties.
     *
     * @return array[]
     */
    public static function webSearchOrders(): array
    {
        $order = [
            'name' => [
                'title' => __('Name'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'age' => [
                'title' => __('Age'),
                'options' => [
                    'Default' => null,
                    'Youngest' => 'asc',
                    'Oldest' => 'desc',
                ],
                'selected' => null,
            ],
            'height' => [
                'title' => __('Height'),
                'options' => [
                    'Default' => null,
                    'Shortest' => 'asc',
                    'Tallest' => 'desc',
                ],
                'selected' => null,
            ],
            'weight' => [
                'title' => __('Weight'),
                'options' => [
                    'Default' => null,
                    'Lightest' => 'asc',
                    'Heaviest' => 'desc',
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
            'status' => [
                'title' => __('Status'),
                'type' => 'select',
                'options' => CharacterStatus::asSelectArray(),
                'selected' => null,
            ],
            'age' => [
                'title' => __('Age'),
                'type' => 'double',
                'selected' => null,
            ],
            'birth_day' => [
                'title' => __('Birth Day'),
                'type' => 'day',
                'selected' => null,
            ],
            'birth_month' => [
                'title' => __('Birth Month'),
                'type' => 'month',
                'selected' => null,
            ],
            'height' => [
                'title' => __('Height (cm)'),
                'type' => 'double',
                'selected' => null,
            ],
            'weight' => [
                'title' => __('Weight (grams)'),
                'type' => 'double',
                'selected' => null,
            ],
            'bust' => [
                'title' => __('Bust'),
                'type' => 'double',
                'selected' => null,
            ],
            'waist' => [
                'title' => __('Waist'),
                'type' => 'double',
                'selected' => null,
            ],
            'hip' => [
                'title' => __('Hip'),
                'type' => 'double',
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
        return $query->withoutGlobalScopes()
            ->with(['mediaStat', 'translations']);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $character = $this->toArray();
        unset($character['media']);
        $character['letter'] = str_index($this->name);
        $character['media_stat'] = $this->mediaStat?->toSearchableArray();
        $character['translations'] = $this->translations
            ->select(['locale', 'title', 'synopsis', 'tagline']);
        $character['created_at'] = $this->created_at?->timestamp;
        $character['updated_at'] = $this->updated_at?->timestamp;
        return $character;
    }

    /**
     * The status of the character.
     *
     * @param int|null $value
     * @return CharacterStatus|null
     */
    public function getStatusAttribute(?int $value): ?CharacterStatus
    {
        return isset($value) ? CharacterStatus::fromValue($value) : null;
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
     * Returns the people the character belongs to.
     *
     * @return BelongsToMany
     */
    function people(): BelongsToMany
    {
        // Pagination doesn't take distinct into account if we don't specify
        // a column explicitly. Noice.
        return $this->belongsToMany(Person::class, AnimeCast::class)
            ->distinct([Person::TABLE_NAME . '.id']);
    }

    /**
     * Returns the anime the character belongs to.
     *
     * @return BelongsToMany
     */
    function anime(): BelongsToMany
    {
        // Pagination doesn't take distinct into account if we don't specify
        // a column explicitly. Noice.
        return $this->belongsToMany(Anime::class, AnimeCast::class)
            ->distinct([Anime::TABLE_NAME . '.id']);
    }

    /**
     * Returns the manga the character belongs to.
     *
     * @return BelongsToMany
     */
    function manga(): BelongsToMany
    {
        // Pagination doesn't take distinct into account if we don't specify
        // a column explicitly. Noice.
        return $this->belongsToMany(Manga::class, MangaCast::class)
            ->distinct([Manga::TABLE_NAME . '.id']);
    }

    /**
     * Returns the games the character belongs to.
     *
     * @return BelongsToMany
     */
    function games(): BelongsToMany
    {
        // Pagination doesn't take distinct into account if we don't specify
        // a column explicitly. Noice.
        return $this->belongsToMany(Game::class, GameCast::class)
            ->distinct([Game::TABLE_NAME . '.id']);
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
     * The model's translation relationship.
     *
     * @return HasOne
     */
    public function translation(): HasOne
    {
        $locale = $this->getLocaleKey();
        if ($this->useFallback()) {
            $countryFallbackLocale = $this->getFallbackLocale($locale);
            $locales = array_unique([$locale, $countryFallbackLocale, $this->getFallbackLocale()]);

            return $this->hasOne(CharacterTranslation::class)
                ->whereIn($this->getTranslationsTable().'.'.$this->getLocaleKey(), $locales);
        }

        return $this->hasOne(CharacterTranslation::class)
            ->where($this->getTranslationsTable().'.'.$this->getLocaleKey(), $locale);
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
        return Url::create(route('characters.details', $this))
            ->setChangeFrequency('weekly')
            ->setLastModificationDate($this->updated_at);
    }
}
