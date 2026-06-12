<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\Actionable;
use App\Traits\Model\HasMediaRatings;
use App\Traits\Model\HasMediaStat;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\HasTvRatedRelations;
use App\Traits\Model\HasViews;
use App\Traits\SearchFilterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
        HasTranslations,
        HasTvRatedRelations,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        Searchable,
        SearchFilterable,
        SoftDeletes;
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
     * Get the song's synced lyrics across all sources.
     *
     * @return HasMany
     */
    public function lyrics(): HasMany
    {
        return $this->hasMany(SongLyric::class);
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
     * @return MorphToMany
     */
    public function anime(): MorphToMany
    {
        return $this->viewableViaParent(
            $this->morphedByMany(Anime::class, 'model', MediaSong::class),
        )->withTimestamps();
    }

    /**
     * Get the game-songs relationship.
     *
     * @return MorphToMany
     */
    public function games(): MorphToMany
    {
        return $this->viewableViaParent(
            $this->morphedByMany(Game::class, 'model', MediaSong::class),
        )->withTimestamps();
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
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saved(function (Song $song): void {
            foreach (['en', 'ja'] as $locale) {
                SongTranslation::firstOrCreate(
                    [
                        'song_id' => $song->id,
                        'locale' => $locale,
                    ],
                    [
                        'title' => $song->original_title,
                    ]
                );
            }
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
            ->setChangeFrequency('daily')
            ->setLastModificationDate($this->updated_at);
    }
}
