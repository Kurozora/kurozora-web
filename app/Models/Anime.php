<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\DayOfWeek;
use App\Enums\MediaCollection;
use App\Enums\SeasonOfYear;
use App\Scopes\IgnoreListScope;
use App\Scopes\TvRatingScope;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\Actionable;
use App\Traits\Model\Favorable;
use App\Traits\Model\HasMediaGenres;
use App\Traits\Model\HasMediaRatings;
use App\Traits\Model\HasMediaRelations;
use App\Traits\Model\HasMediaSongs;
use App\Traits\Model\HasMediaStaff;
use App\Traits\Model\HasMediaStat;
use App\Traits\Model\HasMediaStudios;
use App\Traits\Model\HasMediaTags;
use App\Traits\Model\HasMediaThemes;
use App\Traits\Model\HasSlug;
use App\Traits\Model\HasVideos;
use App\Traits\Model\HasViews;
use App\Traits\Model\Ignored;
use App\Traits\Model\MediaRelated;
use App\Traits\Model\Remindable;
use App\Traits\Model\Trackable;
use App\Traits\Model\TvRated;
use App\Traits\SearchFilterable;
use Astrotomic\Translatable\Translatable;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\SlugOptions;

class Anime extends KModel implements HasMedia, Sitemapable
{
    use Actionable,
        Favorable,
        Remindable,
        HasFactory,
        HasSlug,
        HasMediaGenres,
        HasMediaRatings,
        HasMediaRelations,
        HasMediaSongs,
        HasMediaStaff,
        HasMediaStat,
        HasMediaStudios,
        HasMediaTags,
        HasMediaThemes,
        HasVideos,
        HasViews,
        Ignored,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        MediaRelated,
        Searchable,
        SearchFilterable,
        SoftDeletes,
        Trackable,
        Translatable,
        TvRated;

    // Maximum relationships fetch limit
    const int MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const int|float CACHE_KEY_EPISODES_SECONDS = 60 * 60 * 2;

    // Table name
    const string TABLE_NAME = 'animes';
    protected $table = self::TABLE_NAME;

    /**
     * Translatable attributes.
     *
     * @var array
     */
    public array $translatedAttributes = [
        'title',
        'synopsis',
        'tagline',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'synonym_titles' => AsArrayObject::class,
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
//        'air_time_utc',
//        'broadcast',
//        'duration_string',
//        'duration_total',
//        'information_summary',
//        'time_until_broadcast',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Anime $anime) {
            if (empty($anime->air_season->value == 0)) {
                $anime->air_season = $anime->generateAiringSeason();
            }
        });
    }

    /**
     * The season in which the anime aired.
     *
     * @return ?int
     */
    public function generateAiringSeason(): ?int
    {
        $startedAt = $this->started_at;

        if (empty($startedAt)) {
            return null;
        }

        return season_of_year($startedAt)->value;
    }

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
        $this->addMediaCollection(MediaCollection::Poster)
            ->singleFile();
        $this->addMediaCollection(MediaCollection::Banner)
            ->singleFile();
        $this->addMediaCollection(MediaCollection::Logo)
            ->singleFile();
    }

    /**
     * Get the season in which the anime aired.
     *
     * @param int|null $value
     * @return SeasonOfYear
     */
    public function getAirSeasonAttribute(?int $value): SeasonOfYear
    {
        // For some reason air season is sometimes seen as a string, so force cast to int.
        // Also makes 0 out of null, so win/win.
        return SeasonOfYear::fromValue((int) $value);
    }

    /**
     * Set the season in which the anime airs.
     *
     * @param int|null $value
     * @return void
     */
    public function setAirSeasonAttribute(?int $value): void
    {
        $this->attributes['air_season'] = (int) $value;
    }

    /**
     * The air day of the show.
     *
     * @param int|null $value
     * @return DayOfWeek|null
     */
    public function getAirDayAttribute(?int $value): ?DayOfWeek
    {
        return isset($value) ? DayOfWeek::fromValue($value) : null;
    }

    /**
     * The air time of the anime in UTC timezone.
     *
     * @return string|null
     */
    public function getAirTimeUtcAttribute(): ?string
    {
        if ($this->air_time == '00:00:00' || $this->air_time == '00:00') {
            return null;
        }

        try {
            $airTime = Carbon::createFromFormat('H:i:s', $this->air_time, 'Asia/Tokyo');
        } catch (InvalidFormatException $invalidFormatException) {
            try {
                $airTime = Carbon::createFromFormat('H:i', $this->air_time, 'Asia/Tokyo');
            } catch (InvalidFormatException $invalidFormatException) {
                return null;
            }
        }

        return $airTime->timezone('UTC')->format('H:i');
    }

    /**
     * The broadcast date object of the anime.
     *
     * @return null|Carbon
     */
    public function getBroadcastDateAttribute(): ?Carbon
    {
        $airDay = $this->air_day?->value;
        $airTime = $this->air_time;

        if (is_null($airDay) && empty($airTime)) {
            return null;
        }

        return now('Asia/Tokyo')
            ->next((int) $airDay)
            ->setTimeFromTimeString($airTime ?? '00:00')
            ->setTimezone(config('app.format_timezone'));
    }

    /**
     * The broadcast date and time of the anime as a string.
     *
     * @return null|string
     */
    public function getBroadcastStringAttribute(): ?string
    {
        if ($broadcastDate = $this->broadcast_date) {
            return __(':day at :time', ['day' => $broadcastDate->translatedFormat('l'), 'time' => $broadcastDate->format('H:i T')]);
        }

        return null;
    }

    /**
     * The time from now until the broadcast.
     *
     * @return string
     */
    public function getTimeUntilBroadcastAttribute(): string
    {
        if ($broadcastDate = $this->broadcast_date) {
            $broadcast = $broadcastDate->englishDayOfWeek . ' at ' . $broadcastDate->format('H:i e');
            return now(config('app.format_timezone'))
                ->until($broadcast, CarbonInterface::DIFF_RELATIVE_TO_NOW, true, 3);
        }

        return '';
    }

    /**
     * A summary of the anime's information.
     *
     * Example: 'TV · TV-MA · 25eps · 25min · 2016'
     *
     * @return string
     */
    public function getInformationSummaryAttribute(): string
    {
        $informationSummary = $this->media_type->name . ' · ' . $this->tv_rating->name;
        $episodesCount = $this->episode_count ?? null;
        $duration = $this->duration_string;
        $startedAtYear = $this->started_at;
        $airSeason = $this->air_season->description;

        if (!empty($episodesCount)) {
            $informationSummary .= ' · ' . $episodesCount . ' ' . trans_choice('{1} episode|episodes', $episodesCount);
        }
        if (!empty($duration)) {
            $informationSummary .= ' · ' . $duration;
        }
        if (!empty($startedAtYear)) {
            $informationSummary .= ' · ' . $airSeason . ' ' . $startedAtYear->format('Y');
        }

        return $informationSummary;
    }

    /**
     * Ge the anime's duration as a humanly readable string.
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
     * Get the total runtime of the anime. (duration * episodes)
     *
     * @return string
     * @throws Exception
     */
    public function getDurationTotalAttribute(): string
    {
        if (empty($this->episode_count)) {
            return $this->duration_string;
        }

        return CarbonInterval::seconds($this->duration * $this->episode_count)->cascade()->forHumans();
    }

    /**
     * Get the Anime's characters.
     *
     * @return BelongsToMany
     */
    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, AnimeCast::class)
            ->distinct(['character_id']);
    }

    /**
     * Get the Anime's cast
     *
     * @return HasMany
     */
    public function cast(): HasMany
    {
        return $this->hasMany(AnimeCast::class);
    }

    /**
     * Retrieves the episodes for an Anime item in an array
     *
     * @param array $whereBetween Array containing start and end date. [$startDate, $endDate]
     * @param int|null $limit The number of resources to fetch.
     * @return mixed
     */
    public function getEpisodes(array $whereBetween = [], ?int $limit = null): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.episodes', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'whereBetween' => $whereBetween]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_EPISODES_SECONDS, function () use ($whereBetween, $limit) {
            $episodes = $this->episodes();

            if (!empty($whereBetween)) {
                $episodes->whereBetween('episodes.started_at', $whereBetween);
            }

            return $episodes->limit($limit)->get();
        });
    }

    /**
     * Returns all episodes across all seasons in a flat list.
     *
     * @return HasManyThrough
     */
    public function episodes(): HasManyThrough
    {
        return $this->hasManyThrough(Episode::class, Season::class, 'anime_id', 'season_id');
    }

    /**
     * Get the Anime's seasons
     *
     * @return HasMany
     */
    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class, 'anime_id');
    }

    /**
     * The languages of this Anime
     *
     * @return HasManyThrough
     */
    public function languages(): HasManyThrough
    {
        return $this->hasManyThrough(Language::class, AnimeTranslation::class, 'anime_id', 'code', 'id', 'locale');
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
            ->withoutTvRatings();
    }

    /**
     * Eloquent builder scope that limits the query to the most popular shows.
     *
     * @param Builder $query
     * @param int $limit
     * @param int|null $status
     * @param bool $nsfwAllowed
     * @return Builder
     */
    public function scopeMostPopular(Builder $query, int $limit = 10, ?int $status = 3, bool $nsfwAllowed = false): Builder
    {
        // Get anime with certain airing status.
        if (!empty($status)) {
            $query->where(self::TABLE_NAME . '.status_id', $status);
        }

        // If NSFW is not allowed then filter it out.
        if (!$nsfwAllowed) {
            $query->where(self::TABLE_NAME . '.is_nsfw', false);
        }

        return $query->where(self::TABLE_NAME . '.rank_total', '!=', 0)
            ->orderBy(self::TABLE_NAME . '.rank_total')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to upcoming shows.
     *
     * @param Builder $query
     * @param int $limit
     * @param bool $nsfwAllowed
     * @return Builder
     */
    public function scopeUpcoming(Builder $query, int $limit = 10, bool $nsfwAllowed = false): Builder
    {
        // If NSFW is not allowed then filter it out.
        if (!$nsfwAllowed) {
            $query->where(self::TABLE_NAME . '.is_nsfw', false);
        }

        return $query->where(self::TABLE_NAME . '.started_at', '>=', yesterday())
            ->orderBy(self::TABLE_NAME . '.started_at')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to newly added shows.
     *
     * @param Builder $query
     * @param int $limit
     * @param bool $nsfwAllowed
     * @return Builder
     */
    public function scopeRecentlyAdded(Builder $query, int $limit = 10, bool $nsfwAllowed = false): Builder
    {
        // If NSFW is not allowed then filter it out.
        if (!$nsfwAllowed) {
            $query->where(self::TABLE_NAME . '.is_nsfw', false);
        }

        return $query->orderBy(self::TABLE_NAME . '.created_at', 'desc')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to recently updated shows.
     *
     * @param Builder $query
     * @param int $limit
     * @param bool $nsfwAllowed
     * @return Builder
     */
    public function scopeRecentlyUpdated(Builder $query, int $limit = 10, bool $nsfwAllowed = false): Builder
    {
        // If NSFW is not allowed then filter it out.
        if (!$nsfwAllowed) {
            $query->where(self::TABLE_NAME . '.is_nsfw', false);
        }

        return $query->orderBy(self::TABLE_NAME . '.updated_at', 'desc')
            ->where(self::TABLE_NAME . '.created_at', '<', today())
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to recently finished shows.
     *
     * @param Builder $query
     * @param int $limit
     * @param bool $nsfwAllowed
     * @return Builder
     */
    public function scopeRecentlyFinished(Builder $query, int $limit = 10, bool $nsfwAllowed = false): Builder
    {
        // If NSFW is not allowed then filter it out.
        if (!$nsfwAllowed) {
            $query->where(self::TABLE_NAME . '.is_nsfw', false);
        }

        return $query->orderBy(self::TABLE_NAME . '.ended_at', 'desc')
            ->where(self::TABLE_NAME . '.ended_at', '<=', today()->subDay())
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to shows continuing since past season(s).
     *
     * @param Builder $query
     * @param int $limit
     * @param bool $nsfwAllowed
     * @return Builder
     */
    public function scopeOngoing(Builder $query, int $limit = 10, bool $nsfwAllowed = false): Builder
    {
        // If NSFW is not allowed then filter it out.
        if (!$nsfwAllowed) {
            $query->where(self::TABLE_NAME . '.is_nsfw', false);
        }

        return $query->where(self::TABLE_NAME . '.air_season', '!=', season_of_year()->value)
            ->whereYear(self::TABLE_NAME . '.started_at', '!=', now()->year)
            ->where(self::TABLE_NAME . '.started_at', '<=', now())
            ->where(self::TABLE_NAME . '.status_id', '=', 3)
            ->orderBy(self::TABLE_NAME . '.started_at', 'desc')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to the current season.
     *
     * @param Builder $query
     * @param int $limit
     * @param bool $nsfwAllowed
     * @return Builder
     */
    public function scopeCurrentSeason(Builder $query, int $limit = 10, bool $nsfwAllowed = false): Builder
    {
        // If NSFW is not allowed then filter it out.
        if (!$nsfwAllowed) {
            $query->where(self::TABLE_NAME . '.is_nsfw', false);
        }

        return $query->where(self::TABLE_NAME . '.air_season', '=', season_of_year(today()->addDays(3))->value)
            ->whereYear(self::TABLE_NAME . '.started_at', '=', today()->addDays(3)->year)
            ->limit($limit);
    }

    /**
     * The anime's adaptation source.
     *
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class)->where('type', '=', 'anime');
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
     * The anime's media type.
     *
     * @return BelongsTo
     */
    public function media_type(): BelongsTo
    {
        return $this->belongsTo(MediaType::class);
    }

    /**
     * The anime's adaptation source.
     *
     * @return BelongsTo
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * Get the model's videos in order.
     *
     * @return MorphMany
     */
    public function orderedVideos(): MorphMany
    {
        return $this->videos()
            ->orderBy('order', 'desc');
    }

    /**
     * The anime's translation relationship.
     *
     * @return HasMany
     */
    public function anime_translations(): HasMany
    {
        return $this->hasMany(AnimeTranslation::class);
    }

    /**
     * Returns the Anime items in the user's library.
     *
     * @return BelongsToMany
     */
    function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserLibrary::class, 'trackable_id', 'user_id')
            ->using(UserLibrary::class)
            ->withTimestamps();
    }

    /**
     * Get the model's tags.
     *
     * @return HasManyThrough
     */
    public function tags(): HasManyThrough
    {
        return $this->hasManyThrough(Tag::class, MediaTag::class, 'taggable_id', 'id', 'id', 'tag_id')
            ->where('taggable_type', '=', Anime::class);
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
            ->with(['genres', 'languages', 'mediaStat', 'media_type', 'source', 'status', 'themes', 'translations', 'tv_rating']);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $anime = $this->toArray();
        unset($anime['media']);
        $anime['languages'] = $this->languages
            ->map(function ($item) {
                return $item->toSearchableArray();
            });
        $anime['media_stat'] = $this->mediaStat?->toSearchableArray();
        $anime['translations'] = $this->translations
            ->select(['locale', 'title', 'synopsis', 'tagline']);
        $anime['tv_rating'] = $this->tv_rating?->toSearchableArray();
        $anime['media_type'] = $this->media_type?->toSearchableArray();
        $anime['source'] = $this->source?->toSearchableArray();
        $anime['status'] = $this->status?->toSearchableArray();
        $anime['genres'] = $this->genres
            ->map(function ($item) {
                return $item->toSearchableArray();
            });
        $anime['themes'] = $this->themes
            ->map(function ($item) {
                return $item->toSearchableArray();
            });
//        $anime['tags'] = $this->tags
//            ->map(function ($item) {
//                return $item->toSearchableArray();
//            });
        $anime['started_at'] = $this->started_at?->timestamp;
        $anime['ended_at'] = $this->ended_at?->timestamp;
        $anime['created_at'] = $this->created_at?->timestamp;
        $anime['updated_at'] = $this->updated_at?->timestamp;
        return $anime;
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('anime.details', $this))
            ->setChangeFrequency('daily')
            ->setLastModificationDate($this->updated_at);
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
            'started_at' => [
                'title' => __('First Aired'),
                'options' => [
                    'Default' => null,
                    'Newest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'ended_at' => [
                'title' => __('Last Aired'),
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
        $preferredTvRating = config('app.tv_rating');
        if ($preferredTvRating <= 0) {
            $preferredTvRating = 4;
        }

        $filter = [
            'started_at' => [
                'title' => __('First Aired'),
                'type' => 'date',
                'selected' => null,
            ],
            'ended_at' => [
                'title' => __('Last Aired'),
                'type' => 'date',
                'selected' => null,
            ],
            'duration' => [
                'title' => __('Duration (seconds)'),
                'type' => 'duration',
                'selected' => null,
            ],
            'tv_rating_id' => [
                'title' => __('TV Rating'),
                'type' => 'multiselect',
                'options' => TvRating::where('id', '<=', $preferredTvRating)->pluck('name', 'id'),
                'selected' => null,
            ],
            'media_type_id' => [
                'title' => __('Media Type'),
                'type' => 'multiselect',
                'options' => MediaType::where('type', 'anime')->pluck('name', 'id'),
                'selected' => null,
            ],
            'source_id' => [
                'title' => __('Source'),
                'type' => 'multiselect',
                'options' => Source::all()->pluck('name', 'id'),
                'selected' => null,
            ],
            'status_id' => [
                'title' => __('Airing Status'),
                'type' => 'multiselect',
                'options' => Status::where('type', 'anime')->pluck('name', 'id'),
                'selected' => null,
            ],
            'genres:id' => [
                'title' => __('Genres'),
                'type' => 'multiselect',
                'options' => Genre::all()->sortBy('name')->pluck('name', 'id'),
                'selected' => null
            ],
            'themes:id' => [
                'title' => __('Themes'),
                'type' => 'multiselect',
                'options' => Theme::all()->sortBy('name')->pluck('name', 'id'),
                'selected' => null
            ],
            'air_time' => [
                'title' => __('Air Time'),
                'type' => 'time',
                'selected' => null,
            ],
            'air_day' => [
                'title' => __('Air Day'),
                'type' => 'multiselect',
                'options' => DayOfWeek::asSelectArray(),
                'selected' => null,
            ],
            'air_season' => [
                'title' => __('Air Season'),
                'type' => 'multiselect',
                'options' => SeasonOfYear::asSelectArray(),
                'selected' => null,
            ],
            'season_count' => [
                'title' => __('Season Count'),
                'type' => 'number',
                'selected' => null,
            ],
            'episode_count' => [
                'title' => __('Episode Count'),
                'type' => 'number',
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
}
