<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\Actionable;
use App\Traits\Model\HasMediaRatings;
use App\Traits\Model\HasMediaStat;
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
        HasMediaStat,
        HasSlug,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        Searchable,
        SearchFilterable,
        SoftDeletes,
        Translatable;
    use HasMediaRatings {
        mediaRatings as protected parentMediaRatings;
    }

    // Table name
    const string TABLE_NAME = 'songs';
    protected $table = self::TABLE_NAME;

    /**
     * Translatable attributes.
     *
     * @var array
     */
    public array $translatedAttributes = [
        'title',
        'lyrics',
    ];

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('original_title')
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
        $this->addMediaCollection(MediaCollection::Artwork)
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
            'rank_total' => [
                'title' => __('Ranking'),
                'options' => [
                    'Default' => null,
                    'Highest' => 'asc',
                    'Lowest' => 'desc',
                ],
                'selected' => null,
            ],
            'original_title' => [
                'title' => __('Title'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'artist' => [
                'title' => __('Artist'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
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
        return [];
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
            ->with(['translations', 'mediaStat']);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $song = $this->toArray();
        $song['letter'] = str_index($this->original_title);
        $song['translations'] = $this->translations
            ->select(['locale', 'title', 'lyrics']);
        $song['media_stat'] = $this->mediaStat?->toSearchableArray();
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
     * The media rating relationship of the song.
     *
     * @return MorphMany
     */
    function ratings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'model')
            ->where('model_type', Song::class);
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

            return $this->hasOne(SongTranslation::class)
                ->whereIn($this->getTranslationsTable().'.'.$this->getLocaleKey(), $locales);
        }

        return $this->hasOne(SongTranslation::class)
            ->where($this->getTranslationsTable().'.'.$this->getLocaleKey(), $locale);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('songs.details', $this))
            ->setChangeFrequency('daily')
            ->setLastModificationDate($this->updated_at);
    }
}
