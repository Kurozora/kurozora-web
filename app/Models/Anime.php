<?php

namespace App\Models;

use App\Enums\AnimeImageType;
use App\Enums\DayOfWeek;
use App\Traits\KuroSearchTrait;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

class Anime extends KModel
{
    use HasFactory,
        HasSlug,
        KuroSearchTrait,
        LogsActivity;

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'title' => 10,
            'synopsis' => 5
        ]
    ];

    /**
     * Casts rules.
     *
     * @var array
     */
    protected $casts = [
        'first_aired' => 'date',
        'last_aired' => 'date'
    ];

    protected $appends = [
        'broadcast'
    ];

    // Maximum amount of returned search results
    const MAX_SEARCH_RESULTS = 10;

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 30;

    // Maximum relationships fetch limit
    const MAXIMUM_RELATIONSHIPS_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_EXPLORE_SECONDS = 120 * 60;
    const CACHE_KEY_ACTOR_CHARACTERS_SECONDS = 120 * 60;
    const CACHE_KEY_ACTORS_SECONDS = 120 * 60;
    const CACHE_KEY_CHARACTERS_SECONDS = 120 * 60;
    const CACHE_KEY_EPISODES_SECONDS = 120 * 60;
    const CACHE_KEY_RELATIONS_SECONDS = 120 * 60;
    const CACHE_KEY_SEASONS_SECONDS = 120 * 60;
    const CACHE_KEY_GENRES_SECONDS = 120 * 60;
    const CACHE_KEY_STUDIOS_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'animes';
    protected $table = self::TABLE_NAME;

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
                $tvRating = settings('tv_rating');

                if ($tvRating != -1) {
                    $builder->where('tv_rating_id', '<=', $tvRating);
                }
            }
        });
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
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

        if (!empty($airDay)) {
            $broadcast .= DayOfWeek::fromValue($airDay)->description ?? '-';
        }

        if (!empty($airTime)) {
            $broadcast .= __('at') . $airTime;
        }

        return $broadcast ?? '-';
    }

    /**
     * Returns the users who have this Anime in their favorites.
     *
     * @return BelongsToMany
     */
    public function favoredBy()
    {
        return $this->belongsToMany(User::class, UserFavoriteAnime::TABLE_NAME, 'anime_id', 'user_id');
    }

    /**
     * Returns the moderators of this Anime.
     *
     * @return BelongsToMany
     */
    public function moderators()
    {
        return $this->belongsToMany(User::class, AnimeModerator::TABLE_NAME, 'anime_id', 'user_id')
            ->withPivot('created_at');
    }

    /**
     * Get the Anime's studios
     *
     * @return BelongsToMany
     */
    public function studios()
    {
        return $this->belongsToMany(Studio::class);
    }

    /**
     * Retrieves the studios for an Anime item in an array
     *
     * @return array
     */
    public function getStudios()
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.studios', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_STUDIOS_SECONDS, function () {
            return $this->studios()->get();
        });
    }

    /**
     * Get the Anime's ratings
     *
     * @return HasMany
     */
    public function ratings()
    {
        return $this->hasMany(AnimeRating::class, 'anime_id', 'id');
    }

    /**
     * Get the Anime's images
     *
     * @return HasMany
     */
    public function anime_images()
    {
        return $this->hasMany(AnimeImages::class);
    }

    /**
     * Get the Anime's poster
     *
     * @return HasMany
     */
    public function poster()
    {
        return $this->hasMany(AnimeImages::class, 'anime_id', 'id')->firstWhere('type', '=', AnimeImageType::Poster);
    }

    /**
     * Get the Anime's banner
     *
     * @return HasMany
     */
    public function banner()
    {
        return $this->hasMany(AnimeImages::class, 'anime_id', 'id')->firstWhere('type', '=', AnimeImageType::Banner);
    }

    /**
     * Get the Anime's actors
     *
     * @return HasManyDeep
     */
    public function actors()
    {
        return $this->hasManyDeep(Actor::class, [ActorCharacterAnime::class, ActorCharacter::class], ['anime_id', 'id', 'id'], ['id', 'actor_character_id', 'actor_id'])->distinct();
    }

    /**
     * Retrieves the actors for an Anime item in an array
     *
     * @param int|null $limit
     * @return array
     */
    public function getActors(int $limit = null)
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.actors', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ACTORS_SECONDS, function () use ($limit) {
            return $this->actors()->limit($limit)->get();
        });
    }

    /**
     * Get the Anime's actors
     *
     * @return HasManyDeep
     */
    public function characters()
    {
        return $this->hasManyDeep(Character::class, [ActorCharacterAnime::class, ActorCharacter::class], ['anime_id', 'id', 'id'], ['id', 'actor_character_id', 'character_id'])->distinct();
    }

    /**
     * Retrieves the characters for an Anime item in an array
     *
     * @param int|null $limit
     * @return array
     */
    public function getCharacters(int $limit = null)
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.characters', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->characters()->limit($limit)->get();
        });
    }

    /**
     * Get the Anime's actor characters
     *
     * @return BelongsToMany
     */
    public function actor_characters()
    {
        return $this->belongsToMany(ActorCharacter::class, ActorCharacterAnime::TABLE_NAME, 'anime_id', 'actor_character_id');
    }

    /**
     * Retrieves the actor-characters for an Anime item in an array
     *
     * @param int|null $limit
     * @return array
     */
    public function getActorCharacters(int $limit = null)
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.actor_characters', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ACTOR_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->actor_characters()->limit($limit)->get();
        });
    }

    /**
     * Get the Anime's actor-character-anime
     *
     * @return HasMany
     */
    public function actor_character_anime()
    {
        return $this->hasMany(ActorCharacterAnime::class);
    }

    /**
     * Retrieves the actor-characters-anime for an Anime item in an array
     *
     * @param int|null $limit
     * @return array
     */
    public function getActorCharacterAnime(int $limit = null)
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.actor_character_anime', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_ACTOR_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->actor_character_anime()->limit($limit)->get();
        });
    }

    /**
     * Returns all episodes across all seasons in a flat list.
     *
     * @return HasManyThrough
     */
    public function episodes()
    {
        return $this->hasManyThrough(AnimeEpisode::class, AnimeSeason::class, 'anime_id', 'season_id');
    }

    /**
     * Retrieves the episodes for an Anime item in an array
     *
     * @param array $whereBetween Array containing start and end date. [$startDate, $endDate]
     * @param int|null $limit The number of resources to fetch.
     * @return object
     */
    public function getEpisodes(array $whereBetween = [], int $limit = null)
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
     * Get the Anime's seasons
     *
     * @return HasMany
     */
    public function seasons()
    {
        return $this->hasMany(AnimeSeason::class, 'anime_id');
    }

    /**
     * Returns this anime's seasons
     *
     * @param int|null $limit
     * @return array
     */
    public function getSeasons(int $limit = null)
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.seasons', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_SEASONS_SECONDS, function () use ($limit) {
            return $this->seasons()->limit($limit)->get();
        });
    }

    /**
     * The genres of this Anime
     *
     * @return BelongsToMany
     */
    public function genres()
    {
        return $this->belongsToMany(Genre::class, AnimeGenre::TABLE_NAME, 'anime_id', 'genre_id');
    }

    /**
     * Returns this anime's genres
     *
     * @return array
     */
    public function getGenres()
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.genres', 'id' => $this->id]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_GENRES_SECONDS, function () {
            return $this->genres;
        });
    }

    /**
     * The related anime of this Anime
     *
     * @return HasMany
     */
    public function anime_relations()
    {
        return $this->hasMany(AnimeRelations::class, 'anime_id', 'id');
    }

    /**
     * Returns this anime's related anime
     *
     * @param int|null $limit
     * @return array
     */
    public function getAnimeRelations(int $limit = null)
    {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime.anime_relations', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_RELATIONS_SECONDS, function () use ($limit) {
            return $this->anime_relations()->limit($limit)->get();
        });
    }

    /**
     * Eloquent builder scope that limits the query to
     * the most popular shows.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeMostPopular(Builder $query, int $limit = 10)
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
     * The anime's TV rating.
     *
     * @return BelongsTo
     */
    public function tv_rating(): BelongsTo
    {
        return $this->belongsTo(TvRating::class);
    }
}
