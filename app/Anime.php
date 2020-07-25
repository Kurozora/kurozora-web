<?php

namespace App;

use App\Enums\AnimeImageType;
use App\Traits\KuroSearchTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;

class Anime extends KModel
{
    use KuroSearchTrait, LogsActivity;

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

    // Maximum amount of returned search results
    const MAX_SEARCH_RESULTS = 10;

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 30;

    // Maximum relationship fetch limit
    const MAXIMUM_RELATIONSHIP_LIMIT = 10;

    // How long to cache certain responses
    const CACHE_KEY_EXPLORE_SECONDS = 120 * 60;
    const CACHE_KEY_ACTOR_CHARACTERS_SECONDS = 120 * 60;
    const CACHE_KEY_ACTORS_SECONDS = 120 * 60;
    const CACHE_KEY_CHARACTERS_SECONDS = 120 * 60;
    const CACHE_KEY_RELATIONS_SECONDS = 120 * 60;
    const CACHE_KEY_SEASONS_SECONDS = 120 * 60;
    const CACHE_KEY_GENRES_SECONDS = 120 * 60;
    const CACHE_KEY_STUDIOS_SECONDS = 120 * 60;

    // Table name
    const TABLE_NAME = 'animes';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the users who have this Anime in their favorites.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoritedBy() {
        return $this->belongsToMany(User::class, UserFavoriteAnime::TABLE_NAME, 'anime_id', 'user_id');
    }

    /**
     * Returns the moderators of this Anime.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moderators() {
        return $this->belongsToMany(User::class, AnimeModerator::TABLE_NAME, 'anime_id', 'user_id')
            ->withPivot('created_at');
    }

    /**
     * Get the Anime's studios
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function studios() {
        return $this->hasMany(AnimeStudio::class);
    }

    /**
     * Retrieves the studios for an Anime item in an array
     *
     * @return array
     */
    public function getStudios() {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'studios', 'id' => $this->id]);

        // Retrieve or save cached result
        $studiosInfo = Cache::remember($cacheKey, self::CACHE_KEY_STUDIOS_SECONDS, function () {
            return $this->studios()->get();
        });

        return $studiosInfo;
    }

    /**
     * Get the Anime's ratings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ratings() {
        return $this->hasMany(AnimeRating::class, 'anime_id', 'id');
    }

    /**
     * Get the Anime's images
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function anime_images() {
        return $this->hasMany(AnimeImages::class);
    }

    /**
     * Get the Anime's poster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function poster() {
        return $this->hasMany(AnimeImages::class, 'anime_id', 'id')->firstWhere('type', '=', AnimeImageType::Poster);
    }

    /**
     * Get the Anime's banner
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function banner() {
        return $this->hasMany(AnimeImages::class, 'anime_id', 'id')->firstWhere('type', '=', AnimeImageType::Banner);
    }

    /**
     * Get the Anime's actors
     *
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep
     */
    public function actors() {
        return $this->hasManyDeep(Actor::class, [ActorCharacterAnime::class, ActorCharacter::class], ['anime_id', 'id', 'id'], ['id', 'actor_character_id', 'actor_id'])->distinct();
    }

    /**
     * Retrieves the actors for an Anime item in an array
     *
     * @param int|null $limit
     * @return mixed
     */
    public function getActors(int $limit = null) {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'actors', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        $actorsInfo = Cache::remember($cacheKey, self::CACHE_KEY_ACTORS_SECONDS, function () use ($limit) {
            return $this->actors()->limit($limit)->get();
        });

        return $actorsInfo;
    }

    /**
     * Get the Anime's actors
     *
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep
     */
    public function characters() {
        return $this->hasManyDeep(Character::class, [ActorCharacterAnime::class, ActorCharacter::class], ['anime_id', 'id', 'id'], ['id', 'actor_character_id', 'character_id'])->distinct();
    }

    /**
     * Retrieves the characters for an Anime item in an array
     *
     * @param int|null $limit
     * @return array
     */
    public function getCharacters(int $limit = null) {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'characters', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        $actorsInfo = Cache::remember($cacheKey, self::CACHE_KEY_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->characters()->limit($limit)->get();
        });

        return $actorsInfo;
    }

    /**
     * Get the Anime's actor characters
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function actor_characters() {
        return $this->belongsToMany(ActorCharacter::class, ActorCharacterAnime::TABLE_NAME, 'anime_id', 'actor_character_id');
    }

    /**
     * Retrieves the actor-characters for an Anime item in an array
     *
     * @param int|null $limit
     * @return array
     */
    public function getActorCharacters(int $limit = null) {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'actor_characters', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        $actorCharactersInfo = Cache::remember($cacheKey, self::CACHE_KEY_ACTOR_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->actor_characters()->limit($limit)->get();
        });

        return $actorCharactersInfo;
    }

    /**
     * Get the Anime's actor-character-anime
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actor_character_anime() {
        return $this->hasMany(ActorCharacterAnime::class);
    }

    /**
     * Retrieves the actor-characters-anime for an Anime item in an array
     *
     * @param int|null $limit
     * @return array
     */
    public function getActorCharacterAnime(int $limit = null) {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'actor_character_anime', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        $actorCharacterAnimeInfo = Cache::remember($cacheKey, self::CACHE_KEY_ACTOR_CHARACTERS_SECONDS, function () use ($limit) {
            return $this->actor_character_anime()->limit($limit)->get();
        });

        return $actorCharacterAnimeInfo;
    }

    /**
     * Get the Anime's seasons
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function seasons() {
        return $this->hasMany(AnimeSeason::class, 'anime_id');
    }

    /**
     * Returns this anime's seasons
     *
     * @param int|null $limit
     * @return mixed
     */
    public function getSeasons(int $limit = null) {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'seasons', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        $seasonsInfo = Cache::remember($cacheKey, self::CACHE_KEY_SEASONS_SECONDS, function () use ($limit) {
            return $this->seasons()->limit($limit)->get();
        });

        return $seasonsInfo;
    }

    /**
     * The genres of this Anime
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function genres() {
        return $this->belongsToMany(Genre::class, AnimeGenre::TABLE_NAME, 'anime_id', 'genre_id');
    }

    /**
     * Returns this anime's genres
     *
     * @return mixed
     */
    public function getGenres() {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'genres', 'id' => $this->id]);

        // Retrieve or save cached result
        $genresInfo = Cache::remember($cacheKey, self::CACHE_KEY_GENRES_SECONDS, function () {
            return $this->genres;
        });

        return $genresInfo;
    }

    /**
     * The related anime of this Anime
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function anime_relations() {
        return $this->hasMany(AnimeRelations::class, 'anime_id', 'id');
    }

    /**
     * Returns this anime's related anime
     *
     * @param int|null $limit
     * @return mixed
     */
    public function getAnimeRelations(int $limit = null) {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'anime_relations', 'id' => $this->id, 'limit' => $limit]);

        // Retrieve or save cached result
        $relationsInfo = Cache::remember($cacheKey, self::CACHE_KEY_RELATIONS_SECONDS, function () use ($limit) {
            return $this->anime_relations()->limit($limit)->get();
        });

        return $relationsInfo;
    }

    /**
     * Eloquent builder scope that limits the query to ..
     * .. the most popular shows.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeMostPopular($query, $limit = 10)
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
}
