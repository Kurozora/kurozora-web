<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\DayOfWeek;
use App\Enums\MediaCollection;
use App\Enums\SeasonOfYear;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\Favorable;
use App\Traits\Model\HasMediaGenres;
use App\Traits\Model\HasMediaRelations;
use App\Traits\Model\HasMediaSongs;
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
use Carbon\CarbonInterval;
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

class Game extends KModel implements HasMedia, Sitemapable
{
    use Actionable,
        Favorable,
        HasFactory,
        HasMediaGenres,
        HasMediaRelations,
        HasMediaSongs,
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
        SoftDeletes,
        Translatable,
        Trackable,
        TvRated;

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 130;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_GAME_CAST_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_GAME_SONGS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_CHARACTERS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_PAGES_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_LANGUAGES_SECONDS = 60 * 60 * 24;
    const CACHE_KEY_RELATIONS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_VOLUMES_SECONDS = 60 * 60 * 24;
    const CACHE_KEY_SONGS_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_STAFF_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_STAT_SECONDS = 60 * 60 * 2;
    const CACHE_KEY_STUDIOS_SECONDS = 60 * 60 * 2;

    // Table name
    const TABLE_NAME = 'games';
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
        'published_at' => 'date',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Game $game) {
            if (empty($game->publication_season->value == 0)) {
                $game->publication_season = $game->generatePublishingSeason();
            }
        });
    }

    /**
     * The season in which the game published.
     *
     * @return ?int
     */
    public function generatePublishingSeason(): ?int
    {
        $publishedAt = $this->published_at;

        if (empty($publishedAt)) {
            return null;
        }

        return season_of_year($publishedAt)->value;
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
     * Get the season in which the game is published.
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
     * Set the season in which the game is published.
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
     * A summary of the game's information.
     *
     * Example: 'Game · E (Everyone) · 25vol · 25min · 2016'
     *
     * @return string
     */
    public function getInformationSummaryAttribute(): string
    {
        $informationSummary = $this->media_type->name . ' · ' . $this->tv_rating->name;
        $editionCount = $this->edition_count ?? null;
        $duration = $this->duration_string;
        $publishedAt = $this->published_at;
        $publicationSeason = $this->publication_season->description;

        if (!empty($editionCount)) {
            $informationSummary .= ' · ' . $editionCount . ' ' . trans_choice('{1} edition|editions', $editionCount);
        }
        if (!empty($duration)) {
            $informationSummary .= ' · ' . $duration;
        }
        if (!empty($publishedAt)) {
            $informationSummary .= ' · ' . $publicationSeason . ' ' . $publishedAt->format('Y');
        }

        return $informationSummary;
    }

    /**
     * Ge the game's duration as a humanly readable string.
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
     * Get the total runtime of the game. (duration)
     *
     * @return string
     * @throws Exception
     */
    public function getDurationTotalAttribute(): string
    {
        return $this->duration_string;
    }

    /**
     * The game's adaptation source.
     *
     * @return BelongsTo
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * The game's adaptation source.
     *
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class)
            ->where('type', 'game');
    }

    /**
     * The game's TV rating.
     *
     * @return BelongsTo
     */
    public function tv_rating(): BelongsTo
    {
        return $this->belongsTo(TvRating::class);
    }

    /**
     * The game's media type.
     *
     * @return BelongsTo
     */
    public function media_type(): BelongsTo
    {
        return $this->belongsTo(MediaType::class)
            ->where('type', '=', 'game');
    }

    /**
     * The game's media type.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'parent_id');
    }

    /**
     * Retrieves the studios for a game item in an array
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getStudios(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'game.studios', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STUDIOS_SECONDS, function () use ($limit) {
            return $this->studios()->paginate($limit);
        });
    }

    /**
     * Get the game's ratings
     *
     * @return MorphMany
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'model')
            ->where('model_type', Game::class);
    }

    /**
     * Retrieves the characters for a game item in an array
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getCharacters(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'game.characters', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->characters()->paginate($limit);
        });
    }

    /**
     * Get the game's characters.
     *
     * @return BelongsToMany
     */
    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, GameCast::class);
    }

    /**
     * Retrieves the cast for a game item in an array
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getCast(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'game.cast', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_GAME_CAST_SECONDS, function () use ($limit) {
            return $this->cast()->paginate($limit);
        });
    }

    /**
     * Get the game's cast
     *
     * @return HasMany
     */
    public function cast(): HasMany
    {
        return $this->hasMany(GameCast::class);
    }

    /**
     * The languages of this game
     *
     * @return HasManyThrough
     */
    public function languages(): HasManyThrough
    {
        return $this->hasManyThrough(Language::class, GameTranslation::class, 'game_id', 'code', 'id', 'locale');
    }

    /**
     * Returns this game's languages
     *
     * @return mixed
     */
    public function getLanguages(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'game.languages', 'id' => $this->id]);

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
        $cacheKey = self::cacheKey(['name' => 'game.media-staff', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

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
        $cacheKey = self::cacheKey(['name' => 'game.anime_relations', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

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
        $cacheKey = self::cacheKey(['name' => 'game.manga_relations', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

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
        $cacheKey = self::cacheKey(['name' => 'game.game_relations', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_RELATIONS_SECONDS, function () use ($limit) {
            return $this->gameRelations()->paginate($limit);
        });
    }

    /**
     * The game's translation relationship.
     *
     * @return HasMany
     */
    public function game_translations(): HasMany
    {
        return $this->hasMany(GameTranslation::class);
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
     * Returns the songs relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getSongs(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'game.songs', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_SONGS_SECONDS, function () use ($limit) {
            return $this->songs()->paginate($limit);
        });
    }

    /**
     * Returns the songs relations.
     *
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getMediaSongs(int $limit = 25, int $page = 1): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'game.media-songs', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'limit' => $limit, 'page' => $page]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_GAME_SONGS_SECONDS, function () use ($limit) {
            return $this->mediaSongs()->paginate($limit);
        });
    }

    /**
     * Returns the media stat.
     *
     * @return mixed
     */
    public function getMediaStat(): mixed
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'game.media-stat', 'id' => $this->id]);

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
        $game = $this->toArray();
        $game['published_at'] = $this->published_at?->timestamp;
        $game['created_at'] = $this->created_at?->timestamp;
        $game['updated_at'] = $this->updated_at?->timestamp;
        $game['tags'] = $this->tags()->pluck('name')->toArray();
        return $game;
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
     * Eloquent builder scope that limits the query to upcoming games.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeUpcomingGames(Builder $query, int $limit = 10): Builder
    {
        return $query->whereDate(self::TABLE_NAME . '.published_at', '>', yesterday())
            ->orderBy(self::TABLE_NAME . '.published_at')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to newly added games.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeNewGames(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy(self::TABLE_NAME . '.created_at', 'desc')
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to recently updated games.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeRecentlyUpdatedGames(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy(self::TABLE_NAME . '.updated_at', 'desc')
            ->whereDate(self::TABLE_NAME . '.created_at', '<', today())
            ->limit($limit);
    }

    /**
     * Eloquent builder scope that limits the query to upcoming games.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeGamesSeason(Builder $query, int $limit = 10): Builder
    {
        return $query->where(self::TABLE_NAME . '.publication_season', '=', season_of_year()->value)
            ->whereYear(self::TABLE_NAME . '.published_at', '=', now()->year)
            ->limit($limit);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('games.details', $this))
            ->setChangeFrequency('weekly');
    }
}