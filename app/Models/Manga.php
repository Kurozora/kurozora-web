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
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\SlugOptions;

class Manga extends KModel implements HasMedia, Sitemapable
{
    use Actionable,
        Favorable,
        HasFactory,
        HasMediaGenres,
        HasMediaRatings,
        HasMediaRelations,
        HasMediaStaff,
        HasMediaStat,
        HasMediaStudios,
        HasMediaTags,
        HasMediaThemes,
        HasSlug,
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
        Translatable,
        Trackable,
        TvRated;

    // Maximum relationships fetch limit
    const int MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // Table name
    const string TABLE_NAME = 'mangas';
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
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Manga $manga) {
            if (empty($manga->publication_season->value == 0)) {
                $manga->publication_season = $manga->generatePublishingSeason();
            }
        });
    }

    /**
     * Minimum ratings required to calculate average
     *
     * @return int
     */
    public static function minimumRatingsRequired(): int
    {
        return 999999999;
    }

    /**
     * The season in which the manga published.
     *
     * @return ?int
     */
    public function generatePublishingSeason(): ?int
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
                'title' => __('First Published'),
                'options' => [
                    'Default' => null,
                    'Newest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'ended_at' => [
                'title' => __('Last Published'),
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
                'title' => __('First Published'),
                'type' => 'date',
                'selected' => null,
            ],
            'ended_at' => [
                'title' => __('Last Published'),
                'type' => 'date',
                'selected' => null,
            ],
            'duration' => [
                'title' => __('Duration (seconds)'),
                'type' => 'duration',
                'selected' => null,
            ],
            'country_id' => [
                'title' => __('Country of Origin'),
                'type' => 'multiselect',
                'options' => [
                    'cn' => 'China',
                    'ja' => 'Japan',
                    'kr' => 'Korea',
                    'us' => 'United States',
                ],
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
                'options' => MediaType::where('type', 'manga')->pluck('name', 'id'),
                'selected' => null,
            ],
            'source_id' => [
                'title' => __('Source'),
                'type' => 'multiselect',
                'options' => Source::all()->pluck('name', 'id'),
                'selected' => null,
            ],
            'status_id' => [
                'title' => __('Publication Status'),
                'type' => 'multiselect',
                'options' => Status::where('type', 'manga')->pluck('name', 'id'),
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
            'publication_time' => [
                'title' => __('Publication Time'),
                'type' => 'time',
                'selected' => null,
            ],
            'publication_day' => [
                'title' => __('Publication Day'),
                'type' => 'multiselect',
                'options' => DayOfWeek::asSelectArray(),
                'selected' => null,
            ],
            'publication_season' => [
                'title' => __('Publication Season'),
                'type' => 'multiselect',
                'options' => SeasonOfYear::asSelectArray(),
                'selected' => null,
            ],
            'volume_count' => [
                'title' => __('Volume Count'),
                'type' => 'number',
                'selected' => null,
            ],
            'chapter_count' => [
                'title' => __('Chapter Count'),
                'type' => 'number',
                'selected' => null,
            ],
            'page_count' => [
                'title' => __('Page Count'),
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

    /**
     * Get the season in which the manga is published.
     *
     * @param int|null $value
     * @return SeasonOfYear
     */
    public function getPublicationSeasonAttribute(?int $value): SeasonOfYear
    {
        // For some reason publish season is sometimes seen as a string, so force cast to int.
        // Also makes 0 out of null, so win/win.
        return SeasonOfYear::fromValue((int) $value);
    }

    /**
     * Set the season in which the manga is published.
     *
     * @param int|null $value
     * @return void
     */
    public function setPublicationSeasonAttribute(?int $value): void
    {
        $this->attributes['publication_season'] = (int) $value;
    }

    /**
     * The publication day of the show.
     *
     * @param int|null $value
     * @return DayOfWeek|null
     */
    public function getPublicationDayAttribute(?int $value): ?DayOfWeek
    {
        return isset($value) ? DayOfWeek::fromValue($value) : null;
    }

    /**
     * The publication time of the manga in UTC timezone.
     *
     * @return string|null
     */
    public function getPublicationTimeUtcAttribute(): ?string
    {
        if ($this->publication_time == '00:00:00' || $this->publication_time == '00:00') {
            return null;
        }

        try {
            $publicationTime = Carbon::createFromFormat('H:i:s', $this->publication_time, 'Asia/Tokyo');
        } catch (InvalidFormatException $invalidFormatException) {
            try {
                $publicationTime = Carbon::createFromFormat('H:i', $this->publication_time, 'Asia/Tokyo');
            } catch (InvalidFormatException $invalidFormatException) {
                return null;
            }
        }

        return $publicationTime->timezone('UTC')->format('H:i');
    }

    /**
     * The publication date object of the manga.
     *
     * @return null|Carbon
     */
    public function getPublicationDateAttribute(): ?Carbon
    {
        $publicationDay = $this->publication_day?->value;
        $publicationTime = $this->publication_time;

        if (is_null($publicationDay) && empty($publicationTime)) {
            return null;
        }

        return now('Asia/Tokyo')
            ->next((int) $publicationDay)
            ->setTimeFromTimeString($publicationTime ?? '00:00')
            ->setTimezone(config('app.format_timezone'));
    }

    /**
     * The publication date and time of the manga as a string.
     *
     * @return null|string
     */
    public function getPublicationStringAttribute(): ?string
    {
        if ($publicationDate = $this->publication_date) {
            return __(':day at :time', ['day' => $publicationDate->translatedFormat('l'), 'time' => $publicationDate->format('H:i T')]);
        }

        return null;
    }

    /**
     * The time from now until the publication.
     *
     * @return string
     */
    public function getTimeUntilPublicationAttribute(): string
    {
        if ($publicationDate = $this->publication_date) {
            $publication = $publicationDate->englishDayOfWeek . ' at ' . $publicationDate->format('H:i e');
            return now(config('app.format_timezone'))
                ->until($publication, CarbonInterface::DIFF_RELATIVE_TO_NOW, true, 3);
        }

        return '';
    }

    /**
     * A summary of the manga's information.
     *
     * Example: 'Manga · E (Everyone) · 25vol · 25min · 2016'
     *
     * @return string
     */
    public function getInformationSummaryAttribute(): string
    {
        $informationSummary = $this->media_type->name . ' · ' . $this->tv_rating->name;
        $volumeCount = $this->volume_count ?? null;
        $duration = $this->duration_string;
        $startedAt = $this->started_at;
        $publicationSeason = $this->publication_season->description;

        if (!empty($volumeCount)) {
            $informationSummary .= ' · ' . $volumeCount . ' ' . trans_choice('{1} volume|volumes', $volumeCount);
        }
        if (!empty($duration)) {
            $informationSummary .= ' · ' . $duration;
        }
        if (!empty($startedAt)) {
            $informationSummary .= ' · ' . $publicationSeason . ' ' . $startedAt->format('Y');
        }

        return $informationSummary;
    }

    /**
     * Ge the manga's duration as a humanly readable string.
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
     * Get the total runtime of the manga. (duration * pages)
     *
     * @return string
     * @throws Exception
     */
    public function getDurationTotalAttribute(): string
    {
        if (empty($this->page_count)) {
            return $this->duration_string;
        }

        return CarbonInterval::seconds($this->duration * $this->page_count)->cascade()->forHumans();
    }

    /**
     * The manga's adaptation source.
     *
     * @return BelongsTo
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * The manga's adaptation source.
     *
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class)
            ->where('type', 'manga');
    }

    /**
     * The manga's country of origin.
     *
     * @return BelongsTo
     */
    public function country_of_origin(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'code');
    }

    /**
     * The manga's TV rating.
     *
     * @return BelongsTo
     */
    public function tv_rating(): BelongsTo
    {
        return $this->belongsTo(TvRating::class);
    }

    /**
     * The manga's media type.
     *
     * @return BelongsTo
     */
    public function media_type(): BelongsTo
    {
        return $this->belongsTo(MediaType::class)
            ->where('type', '=', 'manga');
    }

    /**
     * Get the Manga's characters.
     *
     * @return BelongsToMany
     */
    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, MangaCast::class)
            ->distinct(['character_id']);
    }

    /**
     * Get the Manga's cast
     *
     * @return HasMany
     */
    public function cast(): HasMany
    {
        return $this->hasMany(MangaCast::class);
    }

    /**
     * The languages of this Manga
     *
     * @return HasManyThrough
     */
    public function languages(): HasManyThrough
    {
        return $this->hasManyThrough(Language::class, MangaTranslation::class, 'manga_id', 'code', 'id', 'locale');
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
     * The manga's translation relationship.
     *
     * @return HasMany
     */
    public function manga_translations(): HasMany
    {
        return $this->hasMany(MangaTranslation::class);
    }

    /**
     * Get the model's tags.
     *
     * @return HasManyThrough
     */
    public function tags(): HasManyThrough
    {
        return $this->hasManyThrough(Tag::class, MediaTag::class, 'taggable_id', 'id', 'id', 'tag_id')
            ->where('taggable_type', '=', Manga::class);
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
        $manga = $this->toArray();
        unset($manga['media']);
        $manga['languages'] = $this->languages
            ->map(function ($item) {
                return $item->toSearchableArray();
            });
        $manga['media_stat'] = $this->mediaStat?->toSearchableArray();
        $manga['translations'] = $this->translations
            ->select(['locale', 'title', 'synopsis', 'tagline']);
        $manga['tv_rating'] = $this->tv_rating?->toSearchableArray();
        $manga['media_type'] = $this->media_type?->toSearchableArray();
        $manga['source'] = $this->source?->toSearchableArray();
        $manga['status'] = $this->status?->toSearchableArray();
        $manga['genres'] = $this->genres
            ->map(function ($item) {
                return $item->toSearchableArray();
            });
        $manga['themes'] = $this->themes
            ->map(function ($item) {
                return $item->toSearchableArray();
            });
//        $manga['tags'] = $this->tags
//            ->map(function ($item) {
//                return $item->toSearchableArray();
//            });
        $manga['started_at'] = $this->started_at?->timestamp;
        $manga['ended_at'] = $this->ended_at?->timestamp;
        $manga['created_at'] = $this->created_at?->timestamp;
        $manga['updated_at'] = $this->updated_at?->timestamp;
        return $manga;
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
            ->withoutGlobalScopes([TvRatingScope::class, IgnoreListScope::class]);
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

        return $query->leftJoin(MediaStat::TABLE_NAME, MediaStat::TABLE_NAME . '.model_id', '=', self::TABLE_NAME . '.id')
            ->where(MediaStat::TABLE_NAME . '.model_type', '=', $this->getMorphClass())
            ->orderBy(MediaStat::TABLE_NAME . '.in_progress_count', 'desc')
            ->orderBy(MediaStat::TABLE_NAME . '.rating_average', 'desc')
            ->limit($limit)
            ->select(self::TABLE_NAME . '.*');
    }

    /**
     * Eloquent builder scope that limits the query to upcoming mangas.
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
     * Eloquent builder scope that limits the query to newly added mangas.
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
     * Eloquent builder scope that limits the query to recently updated mangas.
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
     * Eloquent builder scope that limits the query to recently finished mangas.
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
            ->where(self::TABLE_NAME . '.ended_at', '<=', today())
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to mangas continuing since past season(s).
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

        return $query->where(self::TABLE_NAME . '.publication_season', '!=', season_of_year()->value)
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

        return $query->where(self::TABLE_NAME . '.publication_season', '=', season_of_year(today()->addDays(3))->value)
            ->whereYear(self::TABLE_NAME . '.started_at', '=', today()->addDays(3)->year)
            ->limit($limit);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('manga.details', $this))
            ->setChangeFrequency('daily')
            ->setLastModificationDate($this->updated_at);
    }
}
