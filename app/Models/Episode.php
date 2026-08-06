<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Support\BreadcrumbNode;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\Actionable;
use App\Traits\Model\HasComments;
use App\Traits\Model\HasMediaRatings;
use App\Traits\Model\HasMediaStat;
use App\Traits\Model\HasPublicID;
use App\Traits\Model\HasSchemaOrg;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\HasVideos;
use App\Traits\Model\HasViews;
use App\Traits\Model\TvRated;
use App\Traits\SearchFilterable;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        HasFactory,
        HasMediaRatings,
        HasMediaStat,
        HasPublicID,
        HasSchemaOrg,
        HasTranslations,
        HasVideos,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        Searchable,
        SearchFilterable,
        SoftDeletes,
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
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_filler' => 'bool',
            'is_nsfw' => 'bool',
            'is_premiere' => 'bool',
            'is_finale' => 'bool',
            'is_special' => 'bool',
            'is_verified' => 'bool',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * Get the started_at attribute with the correct timezone.
     *
     * @return Attribute
     */
    public function startedAt(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value && $value !== 0) {
                    return null;
                }

                $value = $this->asDateTime($value);
                $value->inUserTimezone();
                return $value;
            }
        );
    }

    /**
     * Get the ended_at attribute with the correct timezone.
     *
     * @return Attribute
     */
    public function endedAt(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value && $value !== 0) {
                    return null;
                }

                $value = $this->asDateTime($value);
                $value->inUserTimezone();
                return $value;
            }
        );
    }

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        $creationCallback = function (Episode $episode) {
            if (!empty($episode->started_at)) {
                if (!empty($episode->duration) && empty($episode->ended_at)) {
                    $episode->ended_at = $episode->started_at->addSeconds($episode->duration);
                } else if (empty($episode->duration) && !empty($episode->ended_at)) {
                    $episode->duration = $episode->started_at->diffInSeconds($episode->ended_at, true);
                }
            }
        };
        static::creating($creationCallback);
        static::saving($creationCallback);

        static::creating(function (Episode $episode) {
            if (!is_null($episode->tv_rating_id) && !is_null($episode->is_nsfw)) {
                return;
            }

            $season = $episode->relationLoaded('season')
                ? $episode->season
                : $episode->season()->first(['tv_rating_id', 'is_nsfw']);

            if (!$season) {
                return;
            }

            $episode->tv_rating_id = $season->tv_rating_id;
            $episode->is_nsfw = $season->is_nsfw;
        });
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

        if (request()->tvRating() >= 4) {
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
        $episode['letter'] = str_index($this->title);
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
        return $this->hasOneThrough(Anime::class, Season::class, 'id', 'id', 'season_id', 'anime_id')
            ->withoutGlobalScopes();
    }

    /**
     * Returns the season this episode belongs to.
     *
     * @return BelongsTo
     */
    function season(): BelongsTo
    {
        return $this->belongsTo(Season::class)
            ->withoutGlobalScopes();
    }

    /**
     * Returns the next episode this episode belongs to.
     *
     * @return BelongsTo
     */
    function nextEpisode(): BelongsTo
    {
        return $this->belongsTo(Episode::class)
            ->withoutGlobalScopes();
    }

    /**
     * Returns the previous episode this episode belongs to
     *
     * @return BelongsTo
     */
    function previousEpisode(): BelongsTo
    {
        return $this->belongsTo(Episode::class)
            ->withoutGlobalScopes();
    }

    /**
     * Returns the episode's UserWatchedEpisode relations.
     *
     * @return HasMany
     */
    function userWatchedEpisodes(): HasMany
    {
        return $this->hasMany(UserWatchedEpisode::class);
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
            ->withoutGlobalScopes();
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

    /**
     * The Schema.org type for this entity.
     *
     * @return string
     */
    public function schemaType(): string
    {
        return 'TVEpisode';
    }

    /**
     * The canonical URL for this entity.
     *
     * @return string
     */
    public function schemaUrl(): string
    {
        return route('episodes.details', $this);
    }

    /**
     * The prefix for the Schema.org keywords field.
     *
     * @return string
     */
    public function schemaKeywordsPrefix(): string
    {
        return 'anime,episode';
    }

    /**
     * The label for this entity in a breadcrumb chain.
     *
     * @return string
     */
    public function schemaBreadcrumbLabel(): string
    {
        return __('Episode :n', ['n' => $this->number_total]);
    }

    /**
     * The parent node in the breadcrumb chain.
     *
     * @return BreadcrumbNode
     */
    public function schemaBreadcrumbParent(): BreadcrumbNode
    {
        $anime = $this->anime;

        return new BreadcrumbNode(
            __('Season :n', ['n' => $this->season->number]),
            route('seasons.episodes', $this->season),
            new BreadcrumbNode(
                __('Seasons'),
                route('anime.seasons', $anime),
                new BreadcrumbNode(
                    $anime->title,
                    route('anime.details', $anime),
                    $anime->schemaBreadcrumbParent(),
                ),
            ),
        );
    }

    /**
     * The model whose attributes feed genre, contentRating, studios, and keywords.
     *
     * @return Model
     */
    protected function schemaSubject(): Model
    {
        return $this->anime;
    }

    /**
     * The hero image URL.
     *
     * @return string
     */
    protected function schemaImage(): string
    {
        return $this->getFirstMediaFullUrl(MediaCollection::Banner())
            ?? $this->season?->getFirstMediaFullUrl(MediaCollection::Poster())
            ?? asset('images/static/promotional/social_preview_icon_only.webp');
    }

    /**
     * The trailer embed URL.
     *
     * @return ?string
     */
    protected function schemaTrailerUrl(): ?string
    {
        return $this->videos->first()?->getUrl()
            ?? $this->anime?->videos->first()?->getUrl();
    }
}
