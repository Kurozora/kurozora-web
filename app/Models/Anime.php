<?php

namespace App\Models;

use App\Enums\AnimeImageType;
use App\Enums\DayOfWeek;
use App\Enums\SeasonOfYear;
use App\Traits\Searchable;
use Astrotomic\Translatable\Translatable;
use Auth;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Request;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

class Anime extends KModel
{
    use HasFactory,
        HasSlug,
        LogsActivity,
        Searchable,
        Translatable;

    // Maximum amount of returned search results
    const MAX_SEARCH_RESULTS = 10;

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 30;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_EXPLORE_SECONDS = 120 * 60;
    const CACHE_KEY_ANIME_CAST_SECONDS = 120 * 60;
    const CACHE_KEY_CHARACTERS_SECONDS = 120 * 60;
    const CACHE_KEY_EPISODES_SECONDS = 120 * 60;
    const CACHE_KEY_GENRES_SECONDS = 120 * 60;
    const CACHE_KEY_RELATIONS_SECONDS = 120 * 60;
    const CACHE_KEY_SEASONS_SECONDS = 120 * 60;
    const CACHE_KEY_STAFF_SECONDS = 120 * 60;
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
    ];

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'title' => 10,
            'synopsis' => 5,
        ],
        'joins' => [
            'anime_translations' => [
                'animes.id',
                'anime_translations.anime_id'
            ],
        ],
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
        'broadcast',
        'information_summary',
        'runtime_string',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tv_rating', function (Builder $builder) {
            if (Auth::user() != null) {
                $preferredTvRating = settings('tv_rating');
                $tvRating = TvRating::firstWhere('weight', $preferredTvRating);

                if (!empty($tvRating)) {
                    $builder->where('tv_rating_id', '<=', $tvRating->id);
                }
            }
        });

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

        $winter = Carbon::createFromDate(null, 1, 1);
        $spring = Carbon::createFromDate(null, 4, 4);
        $summer = Carbon::createFromDate(null, 7, 7);
        $fall = Carbon::createFromDate(null, 10, 10);

        return match (true) {
            $firstAired >= $spring && $firstAired < $summer => SeasonOfYear::Spring,
            $firstAired >= $summer && $firstAired < $fall => SeasonOfYear::Summer,
            $firstAired >= $fall && $firstAired < $winter => SeasonOfYear::Fall,
            default => SeasonOfYear::Winter,
        };
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
     * The season in which the anime aired.
     *
     * @param $value
     * @return string|null
     */
    public function getAirSeasonAttribute($value): ?string
    {
        if ($value == null) {
            return null;
        }
        return SeasonOfYear::fromValue($value)->description;
    }

    /**
     * The air time of the anime.
     *
     * @param $value
     * @return string|null
     */
    public function getAirTimeAttribute($value): ?string
    {
        if ($value != '00:00:00') {
            return Carbon::createFromFormat('H:i:s', $value, 'Asia/Tokyo')
                ->timezone('UTC')
                ->format('H:i T');
        }
        return null;
    }

    /**
     * The broadcast date and time of the anime.
     *
     * @return string
     */
    public function getBroadcastAttribute(): string
    {
        $broadcast = null;
        $airDay = $this->air_day;
        $airTime = $this->air_time;
        $dayTime = now('Asia/Tokyo')->next($airDay)
            ->setTimeFromTimeString($airTime ?? '00:00')
            ->setTimezone('UTC');

        if (!empty($airDay) || $airDay == DayOfWeek::Sunday) {
            $broadcast .= $dayTime->getTranslatedDayName();
        }
        if (!empty($airTime)) {
            $broadcast .= ' ' . __('at') . ' ' . $dayTime->format('H:i e');
        }

        return $broadcast ?? '-';
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
        $duration = $this->runtime ?? null;
        $firstAiredYear = $this->first_aired;
        $airSeason = $this->air_season;

        if (!empty($episodesCount)) {
            $informationSummary .= ' · ' . $episodesCount . match ($episodesCount) {
                1 => 'ep',
                default => 'eps',
            };
        }
        if (!empty($duration)) {
            $informationSummary .= ' · ' . $duration / 60 . 'min';
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
    public function getRuntimeStringAttribute(): string
    {
        $runtime = $this->runtime ?? 0;
        return CarbonInterval::seconds($runtime)->cascade()->forHumans();
    }

    /**
     * Returns the users who have this Anime in their favorites.
     *
     * @return BelongsToMany
     */
    public function favoredBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserFavoriteAnime::TABLE_NAME, 'anime_id', 'user_id');
    }

    /**
     * Returns the moderators of this Anime.
     *
     * @return BelongsToMany
     */
    public function moderators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, AnimeModerator::TABLE_NAME, 'anime_id', 'user_id')
            ->withPivot('created_at');
    }

    /**
     * Retrieves the studios for an Anime item in an array
     *
     * @param ?int $limit
     * @return mixed
     */
    public function getStudios(?int $limit = null): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.studios', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STUDIOS_SECONDS, function () use ($limit) {
            return $this->studios()->limit($limit)->get();
        });
    }

    /**
     * Get the Anime's studios
     *
     * @return BelongsToMany
     */
    public function studios(): BelongsToMany
    {
        return $this->belongsToMany(Studio::class);
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
     * @return HasMany
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(AnimeRating::class, 'anime_id', 'id');
    }

    /**
     * Get the Anime's images
     *
     * @return HasMany
     */
    public function anime_images(): HasMany
    {
        return $this->hasMany(AnimeImages::class);
    }

    /**
     * Get the Anime's poster
     *
     * @return ?HasMany
     */
    public function poster(): ?HasMany
    {
        return $this->hasMany(AnimeImages::class, 'anime_id', 'id')->firstWhere('type', '=', AnimeImageType::Poster);
    }

    /**
     * Get the Anime's banner
     *
     * @return ?HasMany
     */
    public function banner(): ?HasMany
    {
        return $this->hasMany(AnimeImages::class, 'anime_id', 'id')->firstWhere('type', '=', AnimeImageType::Banner);
    }

    /**
     * Retrieves the characters for an Anime item in an array
     *
     * @param ?int $limit
     * @return mixed
     */
    public function getCharacters(?int $limit = null): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.characters', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->characters()->limit($limit)->get();
        });
    }

    /**
     * Get the Anime's characters.
     *
     * @return HasManyDeep
     */
    public function characters(): HasManyDeep
    {
        return $this->hasManyDeep(Character::class, [AnimeCast::class], ['anime_id', 'id'], ['id', 'character_id'])->distinct();
    }

    /**
     * Retrieves the cast for an Anime item in an array
     *
     * @param ?int $limit
     * @return mixed
     */
    public function getCast(?int $limit = null): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.cast', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ANIME_CAST_SECONDS, function () use ($limit) {
            return $this->cast()->limit($limit)->get();
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
        $cacheKey = self::cacheKey(['name' => 'anime.episodes', 'id' => $this->id, 'limit' => $limit, 'whereBetween' => $whereBetween]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_EPISODES_SECONDS, function () use ($whereBetween, $limit) {
            $episodes = $this->episodes();

            if (!empty($whereBetween))
                $episodes->whereBetween('first_aired', $whereBetween);

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
     * @param ?int $limit
     * @return mixed
     */
    public function getSeasons(?int $limit = null): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.seasons', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_SEASONS_SECONDS, function () use ($limit) {
            return $this->seasons()->limit($limit)->get();
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
        return $this->hasManyThrough(Genre::class, MediaGenre::class, 'media_id', 'id', 'id', 'genre_id');
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
        return $this->hasMany(MediaGenre::class, 'media_id');
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
     * Returns the anime relations.
     *
     * @param ?int $limit
     * @return mixed
     */
    public function getAnimeRelations(?int $limit = null): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.anime_relations', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_RELATIONS_SECONDS, function () use ($limit) {
            return $this->anime_relations()->limit($limit)->get();
        });
    }

    /**
     * The related anime of this anime.
     *
     * @return HasMany
     */
    public function anime_relations(): HasMany
    {
        return $this->hasMany(MediaRelation::class, 'media_id')->where('related_type', 'anime');
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
     * @return HasMany
     */
    public function relations(): HasMany
    {
        return $this->hasMany(MediaRelation::class, 'media_id');
    }

    /**
     * Eloquent builder scope that limits the query to
     * the most popular shows.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeMostPopular(Builder $query, int $limit = 10): Builder
    {
        // Find the Anime that is most added to user libraries
        $mostAdded = DB::table(UserLibrary::TABLE_NAME)
            ->select('anime_id', DB::raw('count(*) as total'))
            ->groupBy('anime_id')
            ->orderBy('total', 'DESC')
            ->limit($limit)
            ->get();

        // Only keep the IDs of the most added Anime
        $mostAddedIDs = $mostAdded->map(function($item) {
            return $item->anime_id;
        });

        return $query->whereIn(self::TABLE_NAME . '.id', $mostAddedIDs);
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
     * @return HasMany
     */
    public function songs(): HasMany
    {
        return $this->hasMany(AnimeSong::class);
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
     * @param ?int $limit
     * @return mixed
     */
    public function getStaff(?int $limit = null): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.staff', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STAFF_SECONDS, function () use ($limit) {
            return $this->staff()->limit($limit)->get();
        });
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
}
