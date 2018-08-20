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

    protected $tvdb_handle = null;

    /**
     * Retrieves the synopsis for an Anime item
     *
     * @return mixed|string
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
        if($this->runtime != null)
            return $this->runtime;
        // Try to retrieve the runtime from TVDB
        else {
            if($this->tvdb_handle == null)
                $this->tvdb_handle = new TVDB();

            // Get the runtime
            $retRuntime = $this->tvdb_handle->getAnimeDetailValue($this->tvdb_id, 'runtime_minutes');

            // Invalid synopsis
            if($retRuntime == null) $retRuntime = 0;

            // Save the synopsis
            $this->runtime = (int) $retRuntime;
            $this->save();

            // Return it
            return $this->runtime;
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
