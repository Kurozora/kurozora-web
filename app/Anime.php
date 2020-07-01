<?php

namespace App;

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

    // How long to cache certain responses
    const CACHE_KEY_EXPLORE_SECONDS = 120 * 60;
    const CACHE_KEY_ACTORS_SECONDS = 120 * 60;
    const CACHE_KEY_RELATED_ANIME_SECONDS = 120 * 60;
    const CACHE_KEY_SEASONS_SECONDS = 120 * 60;
    const CACHE_KEY_GENRES_SECONDS = 120 * 60;

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
     * Get the Anime's ratings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ratings() {
        return $this->hasMany(AnimeRating::class, 'anime_id', 'id');
    }

    /**
     * Get the Anime's actors
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actors() {
        return $this->hasMany(Actor::class, 'anime_id', 'id');
    }

    /**
     * Retrieves the actors for an Anime item in an array
     *
     * @return array
     */
    public function getActors() {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'actors', 'id' => $this->id]);

        // Retrieve or save cached result
        $actorsInfo = Cache::remember($cacheKey, self::CACHE_KEY_ACTORS_SECONDS, function () {
            return $this->actors()->get();
        });

        return $actorsInfo;
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
     * @return mixed
     */
    public function getSeasons() {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'seasons', 'id' => $this->id]);

        // Retrieve or save cached result
        $seasonsInfo = Cache::remember($cacheKey, self::CACHE_KEY_SEASONS_SECONDS, function () {
            return $this->seasons()->get();
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function related_anime() {
        $animeRelation = $this->belongsToMany(Anime::class, AnimeRelations::class, 'related_anime_id', 'anime_id');
        if($animeRelation->count())
            return $animeRelation;
        return $this->belongsToMany(Anime::class, AnimeRelations::class, 'anime_id', 'related_anime_id');
    }

    /**
     * Returns this anime's related anime
     *
     * @return mixed
     */
    public function getRelatedAnime() {
        // Find location of cached data
        $cacheKey = self::cacheKey(['name' => 'related_anime', 'id' => $this->id]);

        // Retrieve or save cached result
        $relatedAnimeInfo = Cache::remember($cacheKey, self::CACHE_KEY_RELATED_ANIME_SECONDS, function () {
            return $this->related_anime;
        });

        return $relatedAnimeInfo;
    }

    /**
     * Retrieves the poster image URL for an Anime item
     *
     * @param bool $thumbnail
     * @return null|string
     */
    public function getPoster($thumbnail = false) {
        // Try to retrieve the poster from the cache
        if($this->fetched_images) {
            if (!$thumbnail)
                return $this->cached_poster;
            else
                return $this->cached_poster_thumbnail;
        }

        // Images not fetched yet
        return null;
    }

    /**
     * Retrieves the background image URL for an Anime item
     *
     * @param bool $thumbnail
     * @return string
     */
    public function getBackground($thumbnail = false) {
        // Try to retrieve the background from the cache
        if($this->fetched_images) {
            if (!$thumbnail)
                return $this->cached_background;
            else
                return $this->cached_background_thumbnail;
        }

        // Images not fetched yet
        return null;
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
