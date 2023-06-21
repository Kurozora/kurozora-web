<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\DayOfWeek;
use App\Enums\MediaCollection;
use App\Enums\SeasonOfYear;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\Actionable;
use App\Traits\Model\Favorable;
use App\Traits\Model\HasMediaGenres;
use App\Traits\Model\HasMediaRelations;
use App\Traits\Model\HasMediaStaff;
use App\Traits\Model\HasMediaStat;
use App\Traits\Model\HasMediaStudios;
use App\Traits\Model\HasMediaTags;
use App\Traits\Model\HasMediaThemes;
use App\Traits\Model\HasVideos;
use App\Traits\Model\HasViews;
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
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Manga extends KModel implements HasMedia, Sitemapable
{
    use Actionable,
        Favorable,
        HasFactory,
        HasMediaGenres,
        HasMediaRelations,
        HasMediaStaff,
        HasMediaStat,
        HasMediaStudios,
        HasMediaTags,
        HasMediaThemes,
        HasSlug,
        HasUlids,
        HasVideos,
        HasViews,
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

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 130;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_MANGA_CAST_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_CHARACTERS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_PAGES_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_LANGUAGES_SECONDS = 60 * 60 * 24;
    const CACHE_KEY_RELATIONS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_VOLUMES_SECONDS = 60 * 60 * 24;
    const CACHE_KEY_STAFF_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_STAT_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_STUDIOS_SECONDS = 60 * 60 * 2;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool $incrementing
     */
    public $incrementing = false;

    // Table name
    const TABLE_NAME = 'mangas';
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
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'genres',
        'media',
        'mediaStat',
        'translations',
        'tv_rating',
    ];

    /**
     * Casts rules.
     *
     * @var array
     */
    protected $casts = [
        'synonym_titles' => AsArrayObject::class,
        'started_at' => 'date',
        'ended_at' => 'date',
    ];

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
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        if (request()->wantsJson()) {
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
     * Make all instances of the model searchable.
     *
     * @param  int  $chunk
     * @return void
     */
    public static function makeAllSearchable($chunk = null): void
    {
        $self = new static;

        $softDelete = static::usesSoftDelete() && config('scout.soft_delete', false);

        $self->newQuery()
            ->withoutGlobalScopes()
            ->when(true, function ($query) use ($self) {
                $self->makeAllSearchableUsing($query);
            })
            ->when($softDelete, function ($query) {
                $query->withTrashed();
            })
            ->orderBy($self->getKeyName())
            ->searchable($chunk);
    }

    /**
     * The filterable properties.
     *
     * @return array[]
     */
    public static function webSearchFilters(): array
    {
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
            'tv_rating_id' => [
                'title' => __('TV Rating'),
                'type' => 'select',
                'options' => TvRating::all()->pluck('name', 'id'),
                'selected' => null,
            ],
            'media_type_id' => [
                'title' => __('Media Type'),
                'type' => 'select',
                'options' => MediaType::where('type', 'manga')->pluck('name', 'id'),
                'selected' => null,
            ],
            'source_id' => [
                'title' => __('Source'),
                'type' => 'select',
                'options' => Source::all()->pluck('name', 'id'),
                'selected' => null,
            ],
            'status_id' => [
                'title' => __('Airing Status'),
                'type' => 'select',
                'options' => Status::where('type', 'manga')->pluck('name', 'id'),
                'selected' => null,
            ],
            'publication_time' => [
                'title' => __('Publication Time'),
                'type' => 'time',
                'selected' => null,
            ],
            'publication_day' => [
                'title' => __('Publication Day'),
                'type' => 'select',
                'options' => DayOfWeek::asSelectArray(),
                'selected' => null,
            ],
            'publication_season' => [
                'title' => __('Publication Season'),
                'type' => 'select',
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

        if (auth()->user()?->tv_rating >= 4) {
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
     * The publication date and time of the manga.
     *
     * @return null|string
     */
    public function getPublicationAttribute(): ?string
    {
        $publication = null;
        $publicationDay = $this->publication_day?->value;
        $publicationTime = $this->publication_time;
        $dayTime = now('Asia/Tokyo')
            ->next((int) $publicationDay)
            ->setTimeFromTimeString($publicationTime ?? '00:00')
            ->setTimezone('UTC');

        if (!is_null($publicationDay) && !empty($publicationTime)) {
            $publication = $dayTime->englishDayOfWeek . ' at ' . $dayTime->format('H:i e');
        }

        return $publication;
    }

    /**
     * The time from now until the publication.
     *
     * @return string
     */
    public function getTimeUntilPublicationAttribute(): string
    {
        if (empty($this->publication)) {
            return '';
        }

        return now('UTC')->until($this->publication, CarbonInterface::DIFF_RELATIVE_TO_NOW, true, 3);
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
     * Retrieves the studios for a Manga item in an array
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getStudios(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'manga.studios', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STUDIOS_SECONDS, function () use ($limit) {
            return $this->studios()->paginate($limit);
        });
    }

    /**
     * Get the Manga's ratings
     *
     * @return MorphMany
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'model')
            ->where('model_type', Manga::class);
    }

    /**
     * Retrieves the characters for a Manga item in an array
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getCharacters(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'manga.characters', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->characters()->paginate($limit);
        });
    }

    /**
     * Get the Manga's characters.
     *
     * @return BelongsToMany
     */
    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, MangaCast::class);
    }

    /**
     * Retrieves the cast for a Manga item in an array
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getCast(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'manga.cast', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_MANGA_CAST_SECONDS, function () use ($limit) {
            return $this->cast()->paginate($limit);
        });
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
     * Returns this manga's languages
     *
     * @return mixed
     */
    public function getLanguages(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'manga.languages', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_LANGUAGES_SECONDS, function () {
            return $this->languages;
        });
    }

    /**
     * Returns the media staff relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getMediaStaff(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'manga.media-staff', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STAFF_SECONDS, function () use ($limit) {
            return $this->mediaStaff()->paginate($limit);
        });
    }

    /**
     * Returns the anime relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getAnimeRelations(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'manga.anime_relations', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_RELATIONS_SECONDS, function () use ($limit) {
            return $this->animeRelations()->paginate($limit);
        });
    }

    /**
     * Returns the manga relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getMangaRelations(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'manga.manga_relations', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_RELATIONS_SECONDS, function () use ($limit) {
            return $this->mangaRelations()->paginate($limit);
        });
    }

    /**
     * Returns the game relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getGameRelations(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'manga.game_relations', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_RELATIONS_SECONDS, function () use ($limit) {
            return $this->gameRelations()->paginate($limit);
        });
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
     * Returns the media stat.
     *
     * @return mixed
     */
    public function getMediaStat(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'manga.media-stat', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STAT_SECONDS, function () {
            return $this->mediaStat;
        });
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $manga = $this->toArray();
        $manga['started_at'] = $this->started_at?->timestamp;
        $manga['ended_at'] = $this->ended_at?->timestamp;
        $manga['created_at'] = $this->created_at?->timestamp;
        $manga['updated_at'] = $this->updated_at?->timestamp;
        $manga['tags'] = $this->tags()->pluck('name')->toArray();
        return $manga;
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
     * @return Builder
     */
    public function scopeUpcomingManga(Builder $query, int $limit = 10): Builder
    {
        return $query->whereDate(self::TABLE_NAME . '.started_at', '>', yesterday())
            ->orderBy(self::TABLE_NAME . '.started_at')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to newly added mangas.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeNewManga(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy(self::TABLE_NAME . '.created_at', 'desc')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to recently updated mangas.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeRecentlyUpdatedManga(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy(self::TABLE_NAME . '.updated_at', 'desc')
            ->whereDate(self::TABLE_NAME . '.created_at', '<', today())
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to recently finished mangas.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeRecentlyFinishedManga(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy(self::TABLE_NAME . '.ended_at', 'desc')
            ->whereDate(self::TABLE_NAME . '.ended_at', '<=', today())
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to mangas continuing since past season(s).
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeMangaContinuing(Builder $query, int $limit = 10): Builder
    {
        return $query->where(self::TABLE_NAME . '.publication_season', '!=', season_of_year()->value)
            ->whereYear(self::TABLE_NAME . '.started_at', '!=', now()->year)
            ->whereDate(self::TABLE_NAME . '.started_at', '<=', now())
            ->where(self::TABLE_NAME . '.status_id', '=', 3)
            ->orderBy(self::TABLE_NAME . '.started_at', 'desc')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to upcoming mangas.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeMangaSeason(Builder $query, int $limit = 10): Builder
    {
        return $query->where(self::TABLE_NAME . '.publication_season', '=', season_of_year()->value)
            ->whereYear(self::TABLE_NAME . '.started_at', '=', now()->year)
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
            ->setChangeFrequency('weekly');
    }
}
