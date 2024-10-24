<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\MediaCollection;
use App\Enums\StudioType;
use App\Scopes\TvRatingScope;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasMediaRatings;
use App\Traits\Model\HasMediaStat;
use App\Traits\Model\HasSlug;
use App\Traits\Model\HasViews;
use App\Traits\Model\TvRated;
use App\Traits\SearchFilterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\SlugOptions;

class Studio extends KModel implements HasMedia, Sitemapable
{
    use HasFactory,
        HasMediaRatings,
        HasMediaStat,
        HasSlug,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Searchable,
        SearchFilterable,
        SoftDeletes,
        TvRated;

    // Maximum relationships fetch limit
    const int MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // Table name
    const string TABLE_NAME = 'studios';
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
            'social_urls' => AsArrayObject::class,
            'website_urls' => AsArrayObject::class,
            'founded_at' => 'date',
            'defunct_at' => 'date',
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
        $this->addMediaCollection(MediaCollection::Banner)
            ->singleFile();
        $this->addMediaCollection(MediaCollection::Logo)
            ->singleFile();
    }

    /**
     * The studio's predecessors.
     *
     * @return HasMany
     */
    public function predecessors(): HasMany
    {
        return $this->hasMany(Studio::class, 'successor_id');
    }

    /**
     * The studio's successor.
     *
     * @return BelongsTo
     */
    public function successor(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    /**
     * Make all instances of the model searchable.
     *
     * @param  int  $chunk
     * @return void
     */
    public static function makeAllSearchable($chunk = null): void
    {
        $self = new static;

        $softDelete = static::usesSoftDelete() && config('scout.soft_delete', false);

        $self->newQuery()
            ->withoutGlobalScopes()
            ->when(true, function ($query) use ($self) {
                $self->makeAllSearchableUsing($query);
            })
            ->when($softDelete, function ($query) {
                $query->withTrashed();
            })
            ->orderBy($self->getKeyName())
            ->searchable($chunk);
    }

    /**
     * The orderable properties.
     *
     * @return array[]
     */
    public static function webSearchOrders(): array
    {
        $order = [
            'rank_total' => [
                'title' => __('Ranking'),
                'options' => [
                    'Default' => null,
                    'Highest' => 'asc',
                    'Lowest' => 'desc',
                ],
                'selected' => null,
            ],
            'name' => [
                'title' => __('Name'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'address' => [
                'title' => __('Address'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'founded_at' => [
                'title' => __('Founded'),
                'options' => [
                    'Default' => null,
                    'Recent' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'defunct_at' => [
                'title' => __('Defunct'),
                'options' => [
                    'Default' => null,
                    'Recent' => 'desc',
                    'Oldest' => 'asc',
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
        $preferredTvRating = config('app.tv_rating');
        if ($preferredTvRating <= 0) {
            $preferredTvRating = 4;
        }

        $filter = [
            'type' => [
                'title' => __('Type'),
                'type' => 'select',
                'options' => StudioType::asSelectArray(),
                'selected' => null,
            ],
            'tv_rating_id' => [
                'title' => __('TV Rating'),
                'type' => 'multiselect',
                'options' => TvRating::where('id', '<=', $preferredTvRating)->pluck('name', 'id'),
                'selected' => null,
            ],
            'address' => [
                'title' => __('Address'),
                'type' => 'string',
                'selected' => null,
            ],
            'founded_at' => [
                'title' => __('Founded'),
                'type' => 'date',
                'selected' => null,
            ],
            'defunct_at' => [
                'title' => __('Defunct'),
                'type' => 'date',
                'selected' => null,
            ],
        ];

        if (config('app.tv_rating') >= 4) {
            $filter['is_nsfw'] = [
                'title' => __('NSFW'),
                'type' => 'bool',
                'options' => [
                    __('Shown'),
                    __('Hidden'),
                ],
                'selected' => null,
            ];
        }

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
            ->with(['mediaStat', 'tv_rating', 'predecessors', 'successor']);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return $this->withoutRecursion(
            function () {
                $studio = $this->toArray();
                unset($studio['media']);
                $studio['letter'] = str_index($this->name);
                $studio['predecessors'] = $this->predecessors
                    ->map(function ($item) {
                        $item->toSearchableArray();
                    });
                $studio['successor'] = $this->successor?->toSearchableArray();
                $studio['media_stat'] = $this->mediaStat?->toSearchableArray();
                $studio['tv_rating'] = $this->tv_rating?->toSearchableArray();
                $studio['founded_at'] = $this->founded_at?->timestamp;
                $studio['defunct_at'] = $this->defunct_at?->timestamp;
                $studio['created_at'] = $this->created_at?->timestamp;
                $studio['updated_at'] = $this->updated_at?->timestamp;
                return $studio;
            }
        );
    }

    /**
     * The type of the studio.
     *
     * @param int|null $value
     * @return StudioType|null
     */
    public function getTypeAttribute(?int $value): ?StudioType
    {
        return isset($value) ? StudioType::fromValue($value) : null;
    }

    /**
     * Returns the media studios that belongs to the studio
     *
     * @return HasMany
     */
    public function mediaStudios(): HasMany
    {
        return $this->hasMany(MediaStudio::class);
    }

    /**
     * Returns the anime that belongs to the studio
     *
     * @return BelongsToMany
     */
    public function anime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, MediaStudio::class, 'studio_id', 'model_id')
            ->where('model_type', '=', Anime::class)
            ->withTimestamps();
    }

    /**
     * Returns the manga that belongs to the studio
     *
     * @return BelongsToMany
     */
    public function manga(): BelongsToMany
    {
        return $this->belongsToMany(Manga::class, MediaStudio::class, 'studio_id', 'model_id')
            ->where('model_type', '=', Manga::class)
            ->withTimestamps();
    }

    /**
     * Returns the games that belongs to the studio
     *
     * @return BelongsToMany
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, MediaStudio::class, 'studio_id', 'model_id')
            ->where('model_type', '=', Game::class)
            ->withTimestamps();
    }

    /**
     * The anime's TV rating.
     *
     * @return BelongsTo
     */
    public function tv_rating(): BelongsTo
    {
        return $this->belongsTo(TvRating::class);
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  Model|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): \Illuminate\Contracts\Database\Eloquent\Builder
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->withoutGlobalScopes([TvRatingScope::class]);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('studios.details', $this))
            ->setChangeFrequency('daily')
            ->setLastModificationDate($this->updated_at);
    }
}
