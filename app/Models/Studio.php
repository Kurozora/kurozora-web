<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\StudioType;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasBannerImage;
use App\Traits\Model\HasLogoImage;
use App\Traits\Model\HasProfileImage;
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
        HasBannerImage,
        HasLogoImage,
        HasProfileImage,
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
     * Casts rules.
     *
     * @var array
     */
    protected $casts = [
        'founded' => 'date',
        'website_urls' => AsArrayObject::class,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
//        'banner_image',
//        'banner_image_url',
//        'logo_image',
//        'logo_image_url',
//        'profile_image',
//        'profile_image_url',
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
        $this->addMediaCollection($this->bannerImageCollectionName)
            ->singleFile();
        $this->addMediaCollection($this->logoImageCollectionName)
            ->singleFile();
        $this->addMediaCollection($this->profileImageCollectionName)
            ->singleFile();
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
    public function anime_studios(): HasMany
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
