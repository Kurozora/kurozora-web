<?php

namespace App;

use App\Traits\KuroSearchTrait;
use Illuminate\Support\Facades\Cache;

/**
 * Class Anime
 *
 * @package App
 */
class Anime extends KModel
{
    use KuroSearchTrait;

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

    // Maximum amount of returned search results
    const MAX_SEARCH_RESULTS = 10;

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 30;

    // Cache Anime explore page response
    const CACHE_KEY_EXPLORE = 'anime-explore';
    const CACHE_KEY_EXPLORE_MINUTES = 120;

    // Cache Anime actors
    const CACHE_KEY_ACTORS = 'anime-actors-%d';
    const CACHE_KEY_ACTORS_MINUTES = 120;

    // Cache Anime seasons
    const CACHE_KEY_SEASONS = 'anime-seasons-%d';
    const CACHE_KEY_SEASONS_MINUTES = 120;

    // Cache Anime genres
    const CACHE_KEY_GENRES = 'anime-genres-%d';
    const CACHE_KEY_GENRES_MINUTES = 120;

    // Table name
    const TABLE_NAME = 'anime';
    protected $table = self::TABLE_NAME;

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
        $cacheKey = sprintf(self::CACHE_KEY_ACTORS, $this->id);

        // Retrieve or save cached result
        $actorsInfo = Cache::remember($cacheKey, self::CACHE_KEY_ACTORS_MINUTES, function () {
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
        $cacheKey = sprintf(self::CACHE_KEY_SEASONS, $this->id);

        // Retrieve or save cached result
        $seasonsInfo = Cache::remember($cacheKey, self::CACHE_KEY_SEASONS_MINUTES, function () {
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
        return $this->belongsToMany(Genre::class, AnimeGenre::TABLE_NAME, 'anime_id', 'id');
    }

    /**
     * Returns this anime's genres
     *
     * @return mixed
     */
    public function getGenres() {
        // Find location of cached data
        $cacheKey = sprintf(self::CACHE_KEY_GENRES, $this->id);

        // Retrieve or save cached result
        $genresInfo = Cache::remember($cacheKey, self::CACHE_KEY_GENRES_MINUTES, function () {
            return $this->genres;
        });

        return $genresInfo;
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
     * Formats an array of Anime items as a category
     *
     * @param string $categoryTitle
     * @param string $type
     * @param Anime[] $animeArray
     * @return array
     */
    public static function formatAnimesAsCategory($categoryTitle, $type, $animeArray) {
        $retArr = [
            'title' => $categoryTitle,
            'type'  => $type,
            'shows' => []
        ];

        // Add all Anime items to the shows array
        foreach($animeArray as $anime)
            $retArr['shows'][] = self::formatAnimeAsThumbnail($anime);

        // Return the category
        return $retArr;
    }

    /**
     * Formats a list of Anime items into an array of thumbnail data
     *
     * @param Anime[] $animeArray
     * @return array
     */
    public static function formatAnimesAsThumbnail($animeArray) {
        $retArr = [];

        foreach($animeArray as $anime)
            $retArr[] = self::formatAnimeAsThumbnail($anime);

        return $retArr;
    }

    /**
     * Returns minimal data of an Anime item to display it as a thumbnail
     *
     * @param Anime $anime
     * @return array
     */
    public static function formatAnimeAsThumbnail($anime) {
        $genres = $anime->getGenres()->map(function($genre) {
            return $genre->formatForAnimeResponse();
        });

        return [
            'id'                    => $anime->id,
            'title'                 => $anime->title,
            'average_rating'        => $anime->average_rating,
            'poster'                => $anime->getPoster(false),
            'poster_thumbnail'      => $anime->getPoster(true),
            'background'            => $anime->getBackground(false),
            'background_thumbnail'  => $anime->getBackground(true),
            'genres'                => $genres
        ];
    }
}
