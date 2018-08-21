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
    const ANIME_TYPE_SPECIAL    = 3;
    const ANIME_TYPE_OVA        = 4;
    const ANIME_TYPE_ONA        = 5;

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
    public function getActors() {
        // Check if we have saved the actors
        if($this->fetched_actors) {
            return Actor::where('anime_id', $this->id)->get();
        }
        // Try to retrieve the actors from TVDB
        else {
            if ($this->tvdb_handle == null)
                $this->tvdb_handle = new TVDB();

            // Get the actors
            $retActors = $this->tvdb_handle->getAnimeActorData($this->tvdb_id);

            $retArray = [];

            // Actors were fetched
            if($retActors !== null) {
                // Delete old actors if there were any
                Actor::where('anime_id', $this->id)->delete();

                // Insert new actors
                $retArray = [];

                foreach ($retActors as $actor) {
                    $retArray[] = Actor::create([
                        'anime_id' => $this->id,
                        'name' => $actor->name,
                        'role' => $actor->role,
                        'image' => $actor->image
                    ]);
                }
            }

            $this->fetched_actors = true;
            $this->save();

            return $retArray;
        }
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
     * @return string
     */
    public function getSynopsis() {
        // Check if we have saved the synopsis
        if($this->synopsis != null)
            return $this->synopsis;
        // Try to retrieve the synopsis from TVDB
        else {
            if($this->tvdb_handle == null)
                $this->tvdb_handle = new TVDB();

            // Get the synopsis
            $retSynopsis = $this->tvdb_handle->getAnimeDetailValue($this->tvdb_id, 'synopsis');

            // Invalid synopsis
            if($retSynopsis == null) $retSynopsis = 'Unable to retrieve the synopsis...';

            // Save the synopsis
            $this->synopsis = $retSynopsis;
            $this->save();

            // Return it
            return $this->synopsis;
        }
    }

    /**
     * Retrieves the runtime (in minutes) for an Anime item
     *
     * @return integer
     */
    public function getRuntime() {
        // Check if we have saved the runtime
        if($this->runtime !== null)
            return $this->runtime;
        // Try to retrieve the runtime from TVDB
        else {
            if($this->tvdb_handle == null)
                $this->tvdb_handle = new TVDB();

            // Get the runtime
            $retRuntime = $this->tvdb_handle->getAnimeDetailValue($this->tvdb_id, 'runtime_minutes');

            // Invalid runtime
            if($retRuntime == null) $retRuntime = 0;

            // Save the runtime
            $this->runtime = (int) $retRuntime;
            $this->save();

            // Return it
            return $this->runtime;
        }
    }

    /**
     * Retrieves the watch rating for an Anime item
     *
     * @return string
     */
    public function getWatchRating() {
        // Check if we have saved the watch rating
        if($this->watch_rating != null)
            return $this->watch_rating;
        // Try to retrieve the watch rating from TVDB
        else {
            if($this->tvdb_handle == null)
                $this->tvdb_handle = new TVDB();

            // Get the watch rating
            $retWatchRating = $this->tvdb_handle->getAnimeDetailValue($this->tvdb_id, 'watch_rating');

            // Invalid watch rating
            if($retWatchRating == null) $retWatchRating = 'Unknown';

            // Save the watch rating
            $this->watch_rating = $retWatchRating;
            $this->save();

            // Return it
            return $this->watch_rating;
        }
    }

    /**
     * Retrieves the poster image URL for an Anime item
     *
     * @param bool $thumbnail
     * @return string
     */
    public function getPoster($thumbnail = false) {
        // Try to retrieve the poster from the cache
        if(!$thumbnail && $this->cached_poster != null)
            return $this->cached_poster;
        else if($thumbnail && $this->cached_poster_thumbnail != null)
            return $this->cached_poster_thumbnail;

        // Check if there is a TVDB ID set
        if($this->tvdb_id == null)
            return '';

        // Get the poster from TVDB
        if($this->tvdb_handle == null)
            $this->tvdb_handle = new TVDB();

        $retrievedPoster = $this->tvdb_handle->getAnimePoster($this->tvdb_id, $thumbnail);

        // Unable to find the poster
        if($retrievedPoster == null) $retrievedPoster = '0';

        // Cache the poster
        if($thumbnail)
            $this->cached_poster_thumbnail = $retrievedPoster;
        else
            $this->cached_poster = $retrievedPoster;

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
        if(!$thumbnail && $this->cached_background != null)
            return $this->cached_background;
        else if($thumbnail && $this->cached_background_thumbnail != null)
            return $this->cached_background_thumbnail;

        // Check if there is a TVDB ID set
        if($this->tvdb_id == null)
            return '';

        // Get the background from TVDB
        if($this->tvdb_handle == null)
            $this->tvdb_handle = new TVDB();

        $retrievedBg = $this->tvdb_handle->getAnimeBackground($this->tvdb_id, $thumbnail);

        // Unable to find the BG
        if($retrievedBg == null) $retrievedBg = '0';

        // Cache the background
        if($thumbnail)
            $this->cached_background_thumbnail = $retrievedBg;
        else
            $this->cached_background = $retrievedBg;

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
            'id'                => $anime->id,
            'title'             => $anime->title,
            'poster_url'        => $anime->getPoster(true),
            'background_url'    => $anime->getBackground(true)
        ];
    }
}
