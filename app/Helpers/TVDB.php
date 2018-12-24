<?php

use Illuminate\Support\Facades\Config;

class TVDB {
    // Base TVDB API URL
    const API_URL = 'https://api.thetvdb.com';

    // Base TVDB image URL
    const IMG_URL = 'https://www.thetvdb.com/banners';

    // TVDB JWT token
    private $JWTToken = null;

    // Temporarily cached anime details
    private $cachedAnimeDetails = [];

    /**
     * Retrieves the JWT token
     *
     * @return null|string
     */
    public function getToken() {
        if($this->JWTToken != null)
            return $this->JWTToken;

        // Form the authentication string
        $authString = json_encode([
            'apikey'    => Config::get('app.TVDB_API_KEY'),
            'username'  => Config::get('app.TVDB_USER_NAME'),
            'userkey'   => Config::get('app.TVDB_USER_KEY')
        ]);

        // Get the JWT token from TVDB API
        $curl = curl_init(self::API_URL . '/login');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $authString);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($authString)
        ]);
        $result = curl_exec($curl);

        // Try to json decode the result
        $response = json_decode($result);

        if($response != null) {
            if(isset($response->token)) {
                $this->JWTToken = $response->token;
                return $response->token;
            }
            else return null;
        }

        return null;
    }

    /**
     * Returns the URL to an Anime poster, requires its TVDB ID
     *
     * @param int $tvdbID
     * @param bool $thumbnail
     * @return null|string
     */
    public function getAnimePoster($tvdbID, $thumbnail = false) {
        return $this->getAnimeImage('poster', $tvdbID, $thumbnail);
    }

    /**
     * Returns the URL to an Anime poster, requires its TVDB ID
     *
     * @param int $tvdbID
     * @param bool $thumbnail
     * @return null|string
     */
    public function getAnimeBackground($tvdbID, $thumbnail = false) {
        return $this->getAnimeImage('fanart', $tvdbID, $thumbnail);
    }

    /**
     * Gets an Anime image from the TVDB API
     *
     * @param string $type
     * @param int $tvdbID
     * @param bool $thumbnail
     * @return null|string
     */
    private function getAnimeImage($type, $tvdbID, $thumbnail) {
        if($this->getToken() == null)
            return null;

        $curl = curl_init(self::API_URL . '/series/' . $tvdbID . '/images/query?keyType=' . $type);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $this->getToken()
        ]);
        $result = curl_exec($curl);

        // Try to json decode the result
        $response = json_decode($result);

        if($response == null)
            return null;

        // Check if any images were found
        if(!isset($response->data) || !count($response->data))
            return null;

        // Sort the array of images, best to worst score
        $response->data = self::sortImageArrayByScore($response->data);

        if($thumbnail)
            return self::IMG_URL . '/' . $response->data[0]->thumbnail;
        else
            return self::IMG_URL . '/' . $response->data[0]->fileName;
    }

    /**
     * Sorts a TVDB image array by best score to worst
     *
     * @param array $imgArray
     * @return array
     */
    private static function sortImageArrayByScore($imgArray) {
        usort($imgArray, function($a, $b) {
            return $a->ratingsInfo->average < $b->ratingsInfo->average;
        });

        return $imgArray;
    }

    /**
     * Retrieves detailed information about an Anime from TVDB
     *
     * @param int $tvdbID
     * @return null
     */
    private function getAnimeDetails($tvdbID) {
        if($this->getToken() == null)
            return null;

        // Return from the temp cache
        if(isset($this->cachedAnimeDetails[$tvdbID]))
            return $this->cachedAnimeDetails[$tvdbID];

        // Make a request to get the details
        $curl = curl_init(self::API_URL . '/series/' . $tvdbID);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $this->getToken()
        ]);
        $result = curl_exec($curl);

        // Try to json decode the result
        $response = json_decode($result);

        if($response == null || !isset($response->data)) {
            $this->cachedAnimeDetails[$tvdbID] = null;
            return null;
        }

        // Return the details
        $this->cachedAnimeDetails[$tvdbID] = [
            'synopsis'          => $response->data->overview,
            'watch_rating'      => $response->data->rating,
            'runtime_minutes'   => $response->data->runtime,
            'imdb_id'           => $response->data->imdbId,
            'slug'              => $response->data->slug,
            'network'           => $response->data->network,
            'title'             => $response->data->seriesName,
            'status'            => $response->data->status,
            'genres'            => $response->data->genre
        ];

        return $this->cachedAnimeDetails[$tvdbID];
    }

    /**
     * Retrieves a value from the Anime details
     *
     * @param $tvdbID
     * @param $varName
     * @return null
     */
    public function getAnimeDetailValue($tvdbID, $varName) {
        // Get the details for the Anime item
        $animeDetails = $this->getAnimeDetails($tvdbID);

        // If the details were not able to be retrieved or the variable does not exist
        if($animeDetails == null || !isset($animeDetails[$varName]))
            return null;

        // Return the value
        return $animeDetails[$varName];
    }

    /**
     * Fetches the actor data for an Anime item
     *
     * @param $tvdbID
     * @return array|null
     */
    public function getAnimeActorData($tvdbID) {
        if($this->getToken() == null)
            return null;

        // Make a request to get the details
        $curl = curl_init(self::API_URL . '/series/' . $tvdbID . '/actors');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $this->getToken()
        ]);
        $result = curl_exec($curl);

        // Try to json decode the result
        $response = json_decode($result);

        if($response == null || !isset($response->data))
            return null;

        return $response->data;
    }

    /**
     * Fetches the episode data for an Anime item
     *
     * @param $tvdbID
     * @param int $page
     * @return array|null
     */
    public function getAnimeEpisodeData($tvdbID, $page = 1) {
        if($this->getToken() == null)
            return null;

        // Make a request to get the episodes
        $curl = curl_init(self::API_URL . '/series/' . $tvdbID . '/episodes?page=' . $page);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $this->getToken()
        ]);
        $result = curl_exec($curl);

        // Try to json decode the result
        $response = json_decode($result);

        if($response == null || !isset($response->data))
            return null;

        return $response->data;
    }
}