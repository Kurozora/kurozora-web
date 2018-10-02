<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use TVDB;

/**
 * Class Anime
 *
 * @package App
 */
class Anime extends Model
{
    // Types of Anime
    const ANIME_TYPE_UNDEFINED  = 0;
    const ANIME_TYPE_TV         = 1;
    const ANIME_TYPE_MOVIE      = 2;

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 30;

    // Table name
    protected $table = 'anime';

    // Fillable columns
    protected $fillable = [
        'title',
        'cached_poster',
        'cached_poster_thumbnail',
        'cached_background',
        'cached_background_thumbnail',
        'type',
        'nsfw',
        'tvdb_id'
    ];

    // Reusable TVDB handle
    protected $tvdb_handle = null;

    /**
     * Retrieves the actors for an Anime item in an array
     *
     * @return array
     */
    public function getActors($page, $amountPerPage) {
        // Check if we have not yet saved the actors
        if(!$this->fetched_actors) {
            if ($this->tvdb_handle == null)
                $this->tvdb_handle = new TVDB();

            // Get the actors
            $retActors = $this->tvdb_handle->getAnimeActorData($this->tvdb_id);

            // Actors were fetched
            if($retActors !== null) {
                // Delete old actors if there were any
                Actor::where('anime_id', $this->id)->delete();

                foreach ($retActors as $actor) {
                    Actor::create([
                        'anime_id' => $this->id,
                        'name' => $actor->name,
                        'role' => $actor->role,
                        'image' => TVDB::IMG_URL . '/' . $actor->image
                    ]);
                }
            }

            $this->fetched_actors = true;
            $this->save();
        }

        return Actor::where('anime_id', $this->id)
            ->offset($amountPerPage * $page)
            ->limit($amountPerPage)
            ->get();
    }

    /**
     * Get the total Actor count of the Anime
     *
     * @return int
     */
    public function getActorCount() {
        return Actor::where('anime_id', $this->id)->count();
    }

    /**
     * Retrieves the saved episodes for this Anime
     *
     * @return array
     */
    public function getEpisodes($season = null) {
        // Movies don't have episodes
        if($this->type == self::ANIME_TYPE_MOVIE)
            return [];

        // Check if we already have the base episodes
        if($this->fetched_base_episodes) {
            $whereClauses = [
                ['anime_id', '=', $this->id]
            ];

            if($season != null && is_numeric($season))
                $whereClauses[] = ['season', '=', $season];

            $episodeQuery = AnimeEpisode::where($whereClauses);

            if($season != null && is_numeric($season))
                $episodeQuery->orderBy('number', 'ASC');

            return $episodeQuery->get();
        }
        // The episodes still need to be fetched
        else return [];
    }

    /**
     * Retrieves the type of Anime as a string
     *
     * @return string
     */
    public function getType() {
        switch($this->type) {
            case self::ANIME_TYPE_TV:       return 'TV';
            case self::ANIME_TYPE_MOVIE:    return 'Movie';
            case self::ANIME_TYPE_SPECIAL:  return 'Special';
            case self::ANIME_TYPE_OVA:      return 'OVA';
            case self::ANIME_TYPE_ONA:      return 'ONA';
        }

        return 'Unknown type';
    }

    /**
     * Retrieves the synopsis for an Anime item
     *
     * @return null|string
     */
    public function getSynopsis() {
        // The synopsis was already fetched
        if($this->fetched_synopsis)
            return $this->synopsis;
        // Try to retrieve the synopsis from TVDB
        else {
            if($this->tvdb_handle == null)
                $this->tvdb_handle = new TVDB();

            // Get the synopsis
            $retSynopsis = $this->tvdb_handle->getAnimeDetailValue($this->tvdb_id, 'synopsis');

            // Save the synopsis
            $this->synopsis = $retSynopsis;
            $this->fetched_synopsis = true;
            $this->save();

            // Return it
            return $this->synopsis;
        }
    }

    /**
     * Retrieves the runtime (in minutes) for an Anime item
     *
     * @return null|integer
     */
    public function getRuntime() {
        // Check if we have saved the runtime
        if($this->fetched_runtime)
            return $this->runtime;
        // Try to retrieve the runtime from TVDB
        else {
            if($this->tvdb_handle == null)
                $this->tvdb_handle = new TVDB();

            // Get the runtime
            $retRuntime = $this->tvdb_handle->getAnimeDetailValue($this->tvdb_id, 'runtime_minutes');

            if(is_numeric($retRuntime))
                $retRuntime = (int) $retRuntime;

            // Save the runtime
            $this->runtime = $retRuntime;
            $this->fetched_runtime = true;
            $this->save();

            // Return it
            return $this->runtime;
        }
    }

    /**
     * Retrieves the watch rating for an Anime item
     *
     * @return null|string
     */
    public function getWatchRating() {
        // Check if we have saved the watch rating
        if($this->fetched_watch_rating)
            return $this->watch_rating;
        // Try to retrieve the watch rating from TVDB
        else {
            if($this->tvdb_handle == null)
                $this->tvdb_handle = new TVDB();

            // Get the watch rating
            $retWatchRating = $this->tvdb_handle->getAnimeDetailValue($this->tvdb_id, 'watch_rating');

            // Save the watch rating
            $this->watch_rating = $retWatchRating;
            $this->fetched_watch_rating = true;
            $this->save();

            // Return it
            return $this->watch_rating;
        }
    }

    /**
     * Retrieves the poster image URL for an Anime item
     *
     * @param bool $thumbnail
     * @return null|string
     */
    public function getPoster($thumbnail = false) {
        // Try to retrieve the poster from the cache
        if(!$thumbnail && $this->fetched_poster)
            return $this->cached_poster;
        else if($thumbnail && $this->fetched_poster_thumbnail)
            return $this->cached_poster_thumbnail;

        // Check if there is a TVDB ID set
        if($this->tvdb_handle == null)
            $this->tvdb_handle = new TVDB();

        $retrievedPoster = $this->tvdb_handle->getAnimePoster($this->tvdb_id, $thumbnail);

        // Cache the poster
        if($thumbnail) {
            $this->fetched_poster_thumbnail = true;
            $this->cached_poster_thumbnail = $retrievedPoster;
        }
        else {
            $this->fetched_poster = true;
            $this->cached_poster = $retrievedPoster;
        }

        $this->save();

        // Return the poster
        return $retrievedPoster;
    }

    /**
     * Retrieves the background image URL for an Anime item
     *
     * @param bool $thumbnail
     * @return string
     */
    public function getBackground($thumbnail = false) {
        // Try to retrieve the background from the cache
        if(!$thumbnail && $this->fetched_background)
            return $this->cached_background;
        else if($thumbnail && $this->fetched_background_thumbnail)
            return $this->cached_background_thumbnail;

        // Get the background from TVDB
        if($this->tvdb_handle == null)
            $this->tvdb_handle = new TVDB();

        $retrievedBg = $this->tvdb_handle->getAnimeBackground($this->tvdb_id, $thumbnail);

        // Cache the background
        if($thumbnail) {
            $this->fetched_background_thumbnail = true;
            $this->cached_background_thumbnail = $retrievedBg;
        }
        else {
            $this->fetched_background = true;
            $this->cached_background = $retrievedBg;
        }

        $this->save();

        // Return the background
        return $retrievedBg;
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
        return [
            'id'                    => $anime->id,
            'title'                 => $anime->title,
            'average_rating'        => $anime->average_rating,
            'poster'                => $anime->getPoster(false),
            'poster_thumbnail'      => $anime->getPoster(true),
            'background'            => $anime->getBackground(false),
            'background_thumbnail'  => $anime->getBackground(true)
        ];
    }
}
