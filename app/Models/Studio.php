<?php

namespace App\Models;

use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasProfileImage;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Request;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Studio extends KModel implements HasMedia
{
    use HasFactory,
        HasProfileImage,
        HasSlug,
        InteractsWithMedia,
        InteractsWithMediaExtension;

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
        'profile_image',
        'profile_image_url',
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
     * Returns the anime that belongs to the studio
     *
     * @return HasMany
     */
    public function anime_studio(): HasMany
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
        $cacheKey = self::cacheKey(['name' => 'studios.anime', 'id' => $this->id, 'limit' => $limit, 'page' => $page, 'where' => $where]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SECONDS, function () use ($limit, $where) {
            return $this->anime()->where($where)->paginate($limit);
        });
    }
}
