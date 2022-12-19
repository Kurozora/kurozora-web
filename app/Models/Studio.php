<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\MediaCollection;
use App\Enums\StudioType;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasViews;
use App\Traits\Model\TvRated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

class Studio extends KModel implements HasMedia, Sitemapable
{
    use HasFactory,
        HasSlug,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Searchable,
        SoftDeletes,
        TvRated;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_ANIME_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'studios';
    protected $table = self::TABLE_NAME;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'media',
        'tv_rating',
    ];

    /**
     * Casts rules.
     *
     * @var array
     */
    protected $casts = [
        'founded' => 'date',
        'website_urls' => AsArrayObject::class,
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
        $this->addMediaCollection(MediaCollection::Profile)
            ->singleFile();
        $this->addMediaCollection(MediaCollection::Banner)
            ->singleFile();
        $this->addMediaCollection(MediaCollection::Logo)
            ->singleFile();
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
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $studio = $this->toArray();
        $studio['founded'] = $this->founded?->timestamp;
        $studio['created_at'] = $this->created_at?->timestamp;
        $studio['updated_at'] = $this->updated_at?->timestamp;
        return $studio;
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
     * Returns the anime that belongs to the studio
     *
     * @return HasMany
     */
    public function animeStudios(): HasMany
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
     * The anime's TV rating.
     *
     * @return BelongsTo
     */
    public function tv_rating(): BelongsTo
    {
        return $this->belongsTo(TvRating::class);
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
        $cacheKey = self::cacheKey(['name' => 'studios.anime', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page, 'where' => $where]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SECONDS, function () use ($limit, $where) {
            return $this->anime()->where($where)->paginate($limit);
        });
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('studios.details', $this))
            ->setChangeFrequency('weekly');
    }
}
