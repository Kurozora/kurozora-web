<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\DayOfWeek;
use App\Enums\SeasonOfYear;
use App\Scopes\TvRatingScope;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasBannerImage;
use App\Traits\Model\HasPosterImage;
use App\Traits\Model\HasVideos;
use App\Traits\Model\HasViews;
use Astrotomic\Translatable\Translatable;
use Auth;
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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Actions\Actionable;
use Laravel\Scout\Searchable;
use Request;
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
        HasBannerImage,
        HasFactory,
        HasPosterImage,
        HasSlug,
        HasVideos,
        HasViews,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        Searchable,
        Translatable;

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 130;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_EXPLORE_SECONDS = 120 * 60;
    const CACHE_KEY_ANIME_CAST_SECONDS = 120 * 60;
    const CACHE_KEY_ANIME_SONGS_SECONDS = 120 * 60;
    const CACHE_KEY_CHARACTERS_SECONDS = 120 * 60;
    const CACHE_KEY_EPISODES_SECONDS = 120 * 60;
    const CACHE_KEY_GENRES_SECONDS = 120 * 60;
    const CACHE_KEY_THEMES_SECONDS = 120 * 60;
    const CACHE_KEY_LANGUAGES_SECONDS = 120 * 60;
    const CACHE_KEY_RELATIONS_SECONDS = 120 * 60;
    const CACHE_KEY_SEASONS_SECONDS = 120 * 60;
    const CACHE_KEY_SONGS_SECONDS = 120 * 60;
    const CACHE_KEY_STAFF_SECONDS = 120 * 60;
    const CACHE_KEY_STATS_SECONDS = 120 * 60;
    const CACHE_KEY_STUDIOS_SECONDS = 120 * 60;

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
//        'banner_image',
//        'banner_image_url',
//        'broadcast',
//        'duration_string',
//        'duration_total',
//        'information_summary',
//        'poster_image',
//        'poster_image_url',
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

        static::addGlobalScope(new TvRatingScope);

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
    protected function generateAiringSeason(): ?int
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
        if (Request::wantsJson()) {
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
        $this->addMediaCollection($this->posterImageCollectionName)
            ->singleFile();

        $this->addMediaCollection($this->bannerImageCollectionName)
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
        return $anime;
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
            $broadcast = $dayTime->getTranslatedDayName() . ' ' . __('at') . ' ' . $dayTime->format('H:i e');
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
     * Returns the users who have this Anime in their favorites.
     *
     * @return BelongsToMany
     */
    public function favoredBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserFavoriteAnime::TABLE_NAME, 'anime_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Returns the moderators of this Anime.
     *
     * @return BelongsToMany
     */
    public function moderators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, AnimeModerator::TABLE_NAME, 'anime_id', 'user_id')
            ->using(AnimeModerator::class)
            ->withPivot('created_at')
            ->withTimestamps();
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
        $cacheKey = self::cacheKey(['name' => 'anime.studios', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STUDIOS_SECONDS, function () use ($limit) {
            return $this->studios()->paginate($limit);
        });
    }

    /**
     * Get the Anime's studios
     *
     * @return BelongsToMany
     */
    public function studios(): BelongsToMany
    {
        return $this->belongsToMany(Studio::class)
            ->using(AnimeStudio::class)
            ->withPivot('is_licensor', 'is_producer', 'is_studio')
            ->withTimestamps();
    }

    /**
     * Retrieves the studios for an Anime item in an array
     *
     * @param ?int $limit
     * @return mixed
     */
    public function getAnimeStudios(?int $limit = null): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.anime_studios', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STUDIOS_SECONDS, function () use ($limit) {
            return $this->anime_studios()->limit($limit)->get();
        });
    }

    /**
     * Get the Anime's studios
     *
     * @return HasMany
     */
    public function anime_studios(): HasMany
    {
        return $this->hasMany(AnimeStudio::class);
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
        $cacheKey = self::cacheKey(['name' => 'anime.characters', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

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
        $cacheKey = self::cacheKey(['name' => 'anime.cast', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

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
        return $this->hasMany(AnimeCast::class)
            ->where('language_id', 73); // Only japanese cast for now
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
        $cacheKey = self::cacheKey(['name' => 'anime.episodes', 'id' => $this->id, 'limit' => $limit, 'whereBetween' => $whereBetween]);

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
        $cacheKey = self::cacheKey(['name' => 'anime.seasons', 'id' => $this->id, 'limit' => $limit, 'page' => $page, 'reversed' => $reversed]);

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
     * The genres of this Anime
     *
     * @return HasManyThrough
     */
    public function genres(): HasManyThrough
    {
        return $this->hasManyThrough(Genre::class, MediaGenre::class, 'model_id', 'id', 'id', 'genre_id')
            ->where('model_type', '=', Anime::class);
    }

    /**
     * Returns this anime's genres
     *
     * @return mixed
     */
    public function getGenres(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.genres', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_GENRES_SECONDS, function () {
            return $this->genres;
        });
    }

    /**
     * The genres of this Anime
     *
     * @return HasMany
     */
    public function media_genres(): HasMany
    {
        return $this->hasMany(MediaGenre::class, 'model_id')
            ->where('model_type', '=', Anime::class);
    }

    /**
     * Returns this anime's genres
     *
     * @return mixed
     */
    public function getMediaGenres(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.media_genres', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_GENRES_SECONDS, function () {
            return $this->media_genres;
        });
    }

    /**
     * The themes of this Anime
     *
     * @return HasManyThrough
     */
    public function themes(): HasManyThrough
    {
        return $this->hasManyThrough(Theme::class, MediaTheme::class, 'model_id', 'id', 'id', 'theme_id')
            ->where('model_type', '=', Anime::class);
    }

    /**
     * Returns this anime's themes
     *
     * @return mixed
     */
    public function getThemes(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.themes', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_THEMES_SECONDS, function () {
            return $this->themes;
        });
    }

    /**
     * The themes of this Anime
     *
     * @return HasMany
     */
    public function media_themes(): HasMany
    {
        return $this->hasMany(MediaTheme::class, 'model_id')
            ->where('model_type', '=', Anime::class);
    }

    /**
     * Returns this anime's themes
     *
     * @return mixed
     */
    public function getMediaThemes(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.media_themes', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_THEMES_SECONDS, function () {
            return $this->media_themes;
        });
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
        $cacheKey = self::cacheKey(['name' => 'anime.anime_relations', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_RELATIONS_SECONDS, function () use ($limit) {
            return $this->anime_relations()->paginate($limit);
        });
    }

    /**
     * The related anime of this anime.
     *
     * @return morphMany
     */
    public function anime_relations(): morphMany
    {
        return $this->morphMany(MediaRelation::class, 'model')
            ->where('related_type', Anime::class)
            ->join(Anime::TABLE_NAME, function ($join) {
                $join->on(Anime::TABLE_NAME . '.id', '=', MediaRelation::TABLE_NAME . '.related_id');

                if (Auth::check()) {
                    if (settings('tv_rating') >= 0) {
                        $join->where('tv_rating_id', '<=', settings('tv_rating'));
                    }
                }
            });
    }

    /**
     * Returns the media relations.
     *
     * @param ?int $limit
     * @return mixed
     */
    public function getRelations(?int $limit = null): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.relations', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_RELATIONS_SECONDS, function () use ($limit) {
            return $this->relations()->limit($limit)->get();
        });
    }

    /**
     * The media relations of this anime.
     *
     * @return MorphMany
     */
    public function relations(): MorphMany
    {
        return $this->morphMany(MediaRelation::class, 'model');
    }

    /**
     * Returns the media relations.
     *
     * @return mixed
     */
    public function getStats(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.stats', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STATS_SECONDS, function () {
            return $this->stats;
        });
    }

    /**
     * The media stats of this anime.
     *
     * @return HasOne
     */
    public function stats(): HasOne
    {
        return $this->hasOne(MediaStat::class, 'model_id')
            ->where('model_type', Anime::class);
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
            $query->where('status_id', $status);
        }

        // If NSFW is not allowed then filter it out.
        if (!$nsfwAllowed) {
            $query->where('is_nsfw', false);
        }

        return $query->leftJoin(MediaStat::TABLE_NAME, MediaStat::TABLE_NAME . '.model_id', '=', Anime::TABLE_NAME . '.id')
            ->orderBy(MediaStat::TABLE_NAME . '.watching_count', 'desc')
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
        $cacheKey = self::cacheKey(['name' => 'anime.songs', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

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
        $cacheKey = self::cacheKey(['name' => 'anime.anime-songs', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

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
     * The anime's staff relationship.
     *
     * @return HasMany
     */
    public function staff(): HasMany
    {
        return $this->hasMany(AnimeStaff::class);
    }

    /**
     * Returns the staff relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getStaff(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.staff', 'id' => $this->id, 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STAFF_SECONDS, function () use ($limit) {
            return $this->staff()->paginate($limit);
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
        return $this->belongsToMany(User::class, UserLibrary::class, 'anime_id', 'user_id')
            ->using(UserLibrary::class)
            ->withTimestamps();
    }

    /**
     * Returns the Anime items in the user's library.
     *
     * @return HasMany
     */
    function library(): HasMany
    {
        return $this->hasMany(UserLibrary::class);
    }

    /**
     * Scope a query to only include anime proper to user's age.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeWithTvRating(Builder $query)
    {
        $scope = new TvRatingScope();
        $scope->apply($query , $this);
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
