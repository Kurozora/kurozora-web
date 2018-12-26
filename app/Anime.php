<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;
use TVDB;

/**
 * Class Anime
 *
 * @package App
 */
class Anime extends Model
{
    use SearchableTrait;

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

    // Types of Anime
    const ANIME_TYPE_UNDEFINED  = 0;
    const ANIME_TYPE_TV         = 1;
    const ANIME_TYPE_MOVIE      = 2;

    // Status for Anime
    const ANIME_STATUS_TBA      = "TBA";
    const ANIME_STATUS_ENDED    = "Ended";

    // Minimum ratings required to calculate average
    const MINIMUM_RATINGS_REQUIRED = 30;

    // Table name
    const TABLE_NAME = 'anime';
    protected $table = self::TABLE_NAME;

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
    public function getActors() {
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

                // Insert the new actors
                $insertActors = [];

                foreach ($retActors as $actor) {
                    $insertActors[] = [
                        'created_at'    => Carbon::now(),
                        'anime_id'      => $this->id,
                        'name'          => $actor->name,
                        'role'          => $actor->role,
                        'image'         => TVDB::IMG_URL . '/' . $actor->image
                    ];
                }

                Actor::insert($insertActors);
            }

            $this->fetched_actors = true;
            $this->save();
        }

        return Actor::where('anime_id', $this->id)->get();
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
     * Retrieves the type of Anime as a string
     *
     * @return string
     */
    public function getType() {
        switch($this->type) {
            case self::ANIME_TYPE_TV:       return 'TV';
            case self::ANIME_TYPE_MOVIE:    return 'Movie';
        }

        return 'Unknown type';
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
