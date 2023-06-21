<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\Actionable;
use App\Traits\Model\HasViews;
use App\Traits\SearchFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Song extends KModel implements HasMedia, Sitemapable
{
    use Actionable,
        HasFactory,
        HasSlug,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        Searchable,
        SearchFilterable,
        SoftDeletes;

    // How long to cache certain responses
    const CACHE_KEY_ANIMES_SECONDS = 120 * 60;
    const CACHE_KEY_GAMES_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'songs';
    protected $table = self::TABLE_NAME;

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    /**
     * Get the activity options for activity log.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::Poster)
            ->singleFile();
    }

    /**
     * The filterable properties.
     *
     * @return array[]
     */
    public static function webSearchFilters(): array
    {
        return [];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $song = $this->toArray();
        $song['created_at'] = $this->created_at?->timestamp;
        $song['updated_at'] = $this->updated_at?->timestamp;
        return $song;
    }

    /**
     * Get the media-songs relationship.
     *
     * @return HasMany
     */
    public function mediaSongs(): HasMany
    {
        return $this->hasMany(MediaSong::class);
    }

    /**
     * Get the anime-songs relationship.
     *
     * @return BelongsToMany
     */
    public function anime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, MediaSong::class, 'song_id', 'model_id')
            ->where('model_type', '=', Anime::class)
            ->withTimestamps();
    }

    /**
     * Returns the anime relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getAnime(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'song.anime', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIMES_SECONDS, function () use ($limit) {
            return $this->anime()->paginate($limit);
        });
    }

    /**
     * Get the game-songs relationship.
     *
     * @return BelongsToMany
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, MediaSong::class, 'song_id', 'model_id')
            ->where('model_type', '=', Game::class)
            ->withTimestamps();
    }

    /**
     * Returns the game relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getGames(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'song.games', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_GAMES_SECONDS, function () use ($limit) {
            return $this->games()->paginate($limit);
        });
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('songs.details', $this))
            ->setChangeFrequency('weekly');
    }
}
