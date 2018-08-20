<?php

/**
 * @author Musa Semou <mussesemou99@gmail.com>
 */

/**
    Class to interact with The TV DB
**/
class TVDB {
    // Base TVDB API URL
    const API_URL = 'https://api.thetvdb.com';

    // Base TVDB image URL
    const IMG_URL = 'https://www.thetvdb.com/banners';

    // TVDB JWT token
    private $JWTToken = null;

    /**
        Obtains the JWT token
    **/
    public function getToken() {
        if($this->JWTToken != null)
            return $this->JWTToken;

        // Form the authentication string
        $authString = json_encode([
            'apikey'    => env('TVDB_API_KEY', 'ErrorKey'),
            'username'  => env('TVDB_USER_NAME', 'ErrorKey'),
            'userkey'   => env('TVDB_USER_KEY', 'ErrorKey')
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
        Returns the URL to an Anime poster, requires its TVDB ID
    **/
    public function getAnimePoster($tvdbID, $thumbnail = false) {
        return $this->getAnimeImage('poster', $tvdbID, $thumbnail);
    }

    /**
        Returns the URL to an Anime poster, requires its TVDB ID
    **/
    public function getAnimeBackground($tvdbID, $thumbnail = false) {
        return $this->getAnimeImage('fanart', $tvdbID, $thumbnail);
    }

    /**
        Gets an Anime image from the TVDB API
    **/
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
        Sorts a TVDB image array by best score to worst
    **/
    private static function sortImageArrayByScore($imgArray) {
        usort($imgArray, function($a, $b) {
            return $a->ratingsInfo->average < $b->ratingsInfo->average;
        });

        return $imgArray;
    }
}