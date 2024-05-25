<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\Actionable;
use App\Traits\Model\HasComments;
use App\Traits\Model\HasMediaRatings;
use App\Traits\Model\HasMediaStat;
use App\Traits\Model\HasVideos;
use App\Traits\Model\HasViews;
use App\Traits\Model\TvRated;
use App\Traits\SearchFilterable;
use Astrotomic\Translatable\Translatable;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class Episode extends KModel implements HasMedia, Sitemapable
{
    use Actionable,
        HasComments,
        HasMediaRatings,
        HasMediaStat,
        HasFactory,
        HasVideos,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        Searchable,
        SearchFilterable,
        SoftDeletes,
        Translatable,
        TvRated;

    // Table name
    const string TABLE_NAME = 'episodes';
    protected $table = self::TABLE_NAME;

    /**
     * Translatable attributes.
     *
     * @var array
     */
    public array $translatedAttributes = [
        'title',
        'synopsis',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_filler' => 'bool',
        'is_nsfw' => 'bool',
        'is_premiere' => 'bool',
        'is_finale' => 'bool',
        'is_special' => 'bool',
        'is_verified' => 'bool',
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        $creationCallback = function (Episode $episode) {
            if (empty($episode->tv_rating_id)) {
                $episode->tv_rating_id = $episode->anime()->withoutGlobalScopes()->first()->tv_rating_id;
            }

            if (empty($episode->is_nsfw)) {
                $episode->is_nsfw = $episode->anime()->withoutGlobalScopes()->first()->is_nsfw;
            }

            if (!empty($episode->started)) {
                if (!empty($episode->duration) && empty($episode->ended_at)) {
                    $episode->ended_at = $episode->started_at->addSeconds($episode->duration);
                } else if (empty($episode->duration) && !empty($episode->ended_at)) {
                    $episode->duration = $episode->started_at->secondsUntil($episode->ended_at);
                }
            }
        };

        static::creating($creationCallback);
        static::saving($creationCallback);
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
        $this->addMediaCollection(MediaCollection::Banner)
            ->useFallbackUrl($this->anime?->getFirstMediaFullUrl(MediaCollection::Banner()) ??
                $this->anime?->getFirstMediaFullUrl(MediaCollection::Poster()) ??
                ''
            )
            ->singleFile();
    }

    /**
     * Ge the episode's duration as a humanly readable string.
     *
     * @return string
     * @throws Exception
     */
    public function getDurationStringAttribute(): string
    {
        $runtime = $this->duration ?? 0;
        return CarbonInterval::seconds($runtime)->cascade()->forHumans();
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
            'title' => [
                'title' => __('Title'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'number' => [
                'title' => __('Number (Season)'),
                'options' => [
                    'Default' => null,
                    '0-9' => 'asc',
                    '9-0' => 'desc',
                ],
                'selected' => null,
            ],
            'number_total' => [
                'title' => __('Number (Series)'),
                'options' => [
                    'Default' => null,
                    '0-9' => 'asc',
                    '9-0' => 'desc',
                ],
                'selected' => null,
            ],
            'started_at' => [
                'title' => __('First Aired'),
                'options' => [
                    'Default' => null,
                    'Newest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'duration' => [
                'title' => __('Duration'),
                'options' => [
                    'Default' => null,
                    'Shortest' => 'asc',
                    'Longest' => 'desc',
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
            'number' => [
                'title' => __('Number (Season)'),
                'type' => 'number',
                'selected' => null,
            ],
            'number_total' => [
                'title' => __('Number (Series)'),
                'type' => 'number',
                'selected' => null,
            ],
            'started_at' => [
                'title' => __('First Aired'),
                'type' => 'date',
                'selected' => null,
            ],
            'duration' => [
                'title' => __('Duration (seconds)'),
                'type' => 'duration',
                'selected' => null,
            ],
            'is_filler' => [
                'title' => __('Fillers'),
                'type' => 'bool',
                'options' => [
                    __('Shown'),
                    __('Hidden'),
                ],
                'selected' => null,
            ],
            'is_special' => [
                'title' => __('Specials'),
                'type' => 'bool',
                'options' => [
                    __('Shown'),
                    __('Hidden'),
                ],
                'selected' => null,
            ],
            'is_premiere' => [
                'title' => __('Premieres'),
                'type' => 'bool',
                'options' => [
                    __('Shown'),
                    __('Hidden'),
                ],
                'selected' => null,
            ],
            'is_finale' => [
                'title' => __('Finales'),
                'type' => 'bool',
                'options' => [
                    __('Shown'),
                    __('Hidden'),
                ],
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
            ->with(['mediaStat', 'translations']);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $episode = $this->toArray();
        unset($episode['media']);
        $episode['media_stat'] = $this->mediaStat?->toSearchableArray();
        $episode['translations'] = $this->translations
            ->select(['locale', 'title', 'synopsis', 'tagline']);
        $episode['started_at'] = $this->started_at?->timestamp;
        $episode['ended_at'] = $this->ended_at?->timestamp;
        $episode['created_at'] = $this->created_at?->timestamp;
        $episode['updated_at'] = $this->updated_at?->timestamp;
        return $episode;
    }

    /**
     * Returns the anime the episode belongs to.
     *
     * @return HasOneThrough
     */
    function anime(): HasOneThrough
    {
        return $this->hasOneThrough(Anime::class, Season::class, 'id', 'id', 'season_id', 'anime_id');
    }

    /**
     * Returns the season this episode belongs to.
     *
     * @return BelongsTo
     */
    function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * The episode's TV rating.
     *
     * @return BelongsTo
     */
    public function tv_rating(): BelongsTo
    {
        return $this->belongsTo(TvRating::class);
    }

    /**
     * Returns the next episode this episode belongs to.
     *
     * @return BelongsTo
     */
    function next_episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Returns the previous episode this episode belongs to
     *
     * @return BelongsTo
     */
    function previous_episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Get the model's videos.
     *
     * @return MorphMany
     */
    public function videos(): MorphMany
    {
        return $this->morphMany(Video::class, 'videoable')
            ->orderBy('source');
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('episodes.details', $this))
            ->setChangeFrequency('weekly')
            ->setLastModificationDate($this->updated_at);
    }
}
