<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\DayOfWeek;
use App\Enums\MediaCollection;
use App\Enums\SeasonOfYear;
use App\Scopes\TvRatingScope;
use App\Traits\InteractsWithMediaExtension;
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
use Astrotomic\Translatable\Translatable;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Actions\Actionable;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Anime extends KModel implements HasMedia, Sitemapable
{
    use Actionable,
        Favorable,
        HasFactory,
        HasSlug,
        HasMediaGenres,
        HasMediaRelations,
        HasMediaStaff,
        HasMediaStat,
        HasMediaStudios,
        HasMediaTags,
        HasMediaThemes,
        HasVideos,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        MediaRelated,
        Searchable,
        SoftDeletes,
        Trackable,
        Translatable,
        TvRated;

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 130;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_ANIME_CAST_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_ANIME_SONGS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_CHARACTERS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_EPISODES_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_LANGUAGES_SECONDS = 60 * 60 * 24;
    const CACHE_KEY_RELATIONS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_SEASONS_SECONDS = 60 * 60 * 24;
    const CACHE_KEY_SONGS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_STAFF_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_STATS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_STUDIOS_SECONDS = 60 * 60 * 2;

    // Table name
    const TABLE_NAME = 'animes';
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
        'themes',
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
        'first_aired' => 'date',
        'last_aired' => 'date',
    ];

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
            if (empty($anime->air_season)) {
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
        $firstAired = $this->first_aired;

        if (empty($firstAired)) {
            return null;
        }

        return season_of_year($firstAired)->value;
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
     * The broadcast date and time of the anime.
     *
     * @return null|string
     */
    public function getBroadcastAttribute(): ?string
    {
        $broadcast = null;
        $airDay = $this->air_day?->value;
        $airTime = $this->air_time;
        $dayTime = now('Asia/Tokyo')
            ->next((int) $airDay)
            ->setTimeFromTimeString($airTime ?? '00:00')
            ->setTimezone('UTC');

        if (!is_null($airDay) && !empty($airTime)) {
            $broadcast = $dayTime->englishDayOfWeek . ' at ' . $dayTime->format('H:i e');
        }

        return $broadcast;
    }

    /**
     * The time from now until the broadcast.
     *
     * @return string
     */
    public function getTimeUntilBroadcastAttribute(): string
    {
        if (empty($this->broadcast)) {
            return '';
        }

        return now('UTC')->until($this->broadcast, CarbonInterface::DIFF_RELATIVE_TO_NOW, true, 3);
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
        $firstAiredYear = $this->first_aired;
        $airSeason = $this->air_season->description;

        if (!empty($episodesCount)) {
            $informationSummary .= ' · ' . $episodesCount . ' ' . trans_choice('{1} episode|episodes', $episodesCount);
        }
        if (!empty($duration)) {
            $informationSummary .= ' · ' . $duration;
        }
        if (!empty($firstAiredYear)) {
            $informationSummary .= ' · ' . $airSeason . ' ' . $firstAiredYear->format('Y');
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
     * Retrieves the studios for an Anime item in an array
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getStudios(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.studios', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STUDIOS_SECONDS, function () use ($limit) {
            return $this->studios()->paginate($limit);
        });
    }

    /**
     * Get the Anime's ratings
     *
     * @return MorphMany
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'model')
            ->where('model_type', Anime::class);
    }

    /**
     * Retrieves the characters for an Anime item in an array
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getCharacters(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.characters', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->characters()->paginate($limit);
        });
    }

    /**
     * Get the Anime's characters.
     *
     * @return BelongsToMany
     */
    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, AnimeCast::class)
            ->distinct();
    }

    /**
     * Retrieves the cast for an Anime item in an array
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getCast(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.cast', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_CAST_SECONDS, function () use ($limit) {
            return $this->cast()->paginate($limit);
        });
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
                $episodes->whereBetween('episodes.first_aired', $whereBetween);
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
     * Returns this anime's seasons
     *
     * @param int $limit
     * @param int $page
     * @param bool $reversed
     * @return mixed
     */
    public function getSeasons(int $limit = 25, int $page = 1, bool $reversed = false): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.seasons', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page, 'reversed' => $reversed]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_SEASONS_SECONDS, function () use ($reversed, $limit) {
            if ($reversed) {
                return $this->seasons()->orderByDesc('number')->paginate($limit);
            }
            return $this->seasons()->orderBy('number')->paginate($limit);
        });
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
     * Returns this anime's languages
     *
     * @return mixed
     */
    public function getLanguages(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.languages', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_LANGUAGES_SECONDS, function () {
            return $this->languages;
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
        $cacheKey = self::cacheKey(['name' => 'anime.anime_relations', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

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
        $cacheKey = self::cacheKey(['name' => 'anime.manga_relations', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

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
        $cacheKey = self::cacheKey(['name' => 'anime.game_relations', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_RELATIONS_SECONDS, function () use ($limit) {
            return $this->gameRelations()->paginate($limit);
        });
    }

    /**
     * Returns the media relations.
     *
     * @return mixed
     */
    public function getMediaStat(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.media-stat', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STATS_SECONDS, function () {
            return $this->mediaStat;
        });
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
            $query->where(Anime::TABLE_NAME . '.status_id', $status);
        }

        // If NSFW is not allowed then filter it out.
        if (!$nsfwAllowed) {
            $query->where(Anime::TABLE_NAME . '.is_nsfw', false);
        }

        return $query->leftJoin(MediaStat::TABLE_NAME, MediaStat::TABLE_NAME . '.model_id', '=', Anime::TABLE_NAME . '.id')
            ->where(MediaStat::TABLE_NAME . '.model_type', '=', $this->getMorphClass())
            ->orderBy(MediaStat::TABLE_NAME . '.in_progress_count', 'desc')
            ->orderBy(MediaStat::TABLE_NAME . '.rating_average', 'desc')
            ->limit($limit)
            ->select(Anime::TABLE_NAME . '.*');
    }

    /**
     * Eloquent builder scope that limits the query to the given genre.
     *
     * @param Builder $query
     * @param Genre $genre
     * @return Builder
     */
    public function scopeWhereGenre(Builder $query, Genre $genre): Builder
    {
        return $query->whereRelation('genres', 'genre_id', '=', $genre->id);
    }

    /**
     * Eloquent builder scope that limits the query to the given theme.
     *
     * @param Builder $query
     * @param Theme $theme
     * @return Builder
     */
    public function scopeWhereTheme(Builder $query, Theme $theme): Builder
    {
        return $query->whereRelation('themes', 'theme_id', '=', $theme->id);
    }

    /**
     * Eloquent builder scope that limits the query to upcoming shows.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeUpcomingShows(Builder $query, int $limit = 10): Builder
    {
        return $query->whereDate('first_aired', '>', yesterday())
            ->orderBy('first_aired')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to newly added shows.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeNewShows(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('created_at', 'desc')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to recently updated shows.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeRecentlyUpdatedShows(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('updated_at', 'desc')
            ->whereDate('created_at', '<', today())
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to recently finished shows.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeRecentlyFinishedShows(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('last_aired', 'desc')
            ->whereDate('last_aired', '<=', today())
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to shows continuing since past season(s).
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeAnimeContinuing(Builder $query, int $limit = 10): Builder
    {
        return $query->where('air_season', '!=', season_of_year()->value)
            ->whereYear('first_aired', '!=', now()->year)
            ->whereDate('first_aired', '<=', now())
            ->where('status_id', '=', 3)
            ->orderBy('first_aired', 'desc')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to upcoming shows.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeAnimeSeason(Builder $query, int $limit = 10): Builder
    {
        return $query->where('air_season', '=', season_of_year()->value)
            ->whereYear('first_aired', '=', now()->year)
            ->limit($limit);
    }

    /**
     * The anime's adaptation source.
     *
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class)->where('type', 'anime');
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
     * The anime's songs relationship.
     *
     * @return HasManyThrough
     */
    public function songs(): HasManyThrough
    {
        return $this->hasManyThrough(Song::class, AnimeSong::class, 'anime_id', 'id', 'id', 'song_id');
    }

    /**
     * Returns the songs relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getSongs(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.songs', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_SONGS_SECONDS, function () use ($limit) {
            return $this->songs()->paginate($limit);
        });
    }

    /**
     * The anime's anime-songs relationship.
     *
     * @return HasMany
     */
    public function anime_songs(): HasMany
    {
        return $this->hasMany(AnimeSong::class);
    }

    /**
     * Returns the songs relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getAnimeSongs(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.anime-songs', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_SONGS_SECONDS, function () use ($limit) {
            return $this->anime_songs()->paginate($limit);
        });
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
     * Returns the media staff relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getMediaStaff(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.media-staff', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STAFF_SECONDS, function () use ($limit) {
            return $this->mediaStaff()->paginate($limit);
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
     * Scope a query to only include anime proper to user's age.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeWithTvRating(Builder $query): void
    {
        $scope = new TvRatingScope();
        $scope->apply($query , $this);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $anime = $this->toArray();
        $anime['first_aired'] = $this->first_aired?->timestamp;
        $anime['last_aired'] = $this->last_aired?->timestamp;
        $anime['created_at'] = $this->created_at?->timestamp;
        $anime['updated_at'] = $this->updated_at?->timestamp;
        $anime['tags'] = $this->tags()->pluck('name')->toArray();
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
            ->setChangeFrequency('weekly');
    }
}
