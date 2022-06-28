<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Actions\Actionable;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Song extends KModel implements Sitemapable
{
    use Actionable,
        HasFactory,
        HasSlug,
        LogsActivity,
        Searchable;

    // How long to cache certain responses
    const CACHE_KEY_ANIMES_SECONDS = 120 * 60;

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
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('songs.details', $this))
            ->setChangeFrequency('weekly');
    }

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return 'songs_index';
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
            'am_id' => $this->am_id,
            'mal_id' => $this->mal_id,
            'slug' => $this->slug,
            'title' => $this->title,
            'artist' => $this->artist
        ];
    }

    /**
     * Get the anime-songs relationship.
     *
     * @return HasMany
     */
    public function anime_songs(): HasMany
    {
        return $this->hasMany(AnimeSong::class);
    }

    /**
     * Get the anime-songs relationship.
     *
     * @return HasManyThrough
     */
    public function anime(): HasManyThrough
    {
        return $this->hasManyThrough(Anime::class, AnimeSong::class, 'song_id', 'id', 'id', 'anime_id');
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
        $cacheKey = self::cacheKey(['name' => 'song.anime', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIMES_SECONDS, function () use ($limit) {
            return $this->anime()->paginate($limit);
        });
    }
}
