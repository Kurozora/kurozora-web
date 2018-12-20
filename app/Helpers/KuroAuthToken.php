<?php

class KuroAuthToken {
    const VALIDITY_REGEX = '/^[0-9]+:[^:]+$/';

    /**
     * Generates a Kuro Auth Token based on the given details
     *
     * @param $userID
     * @param $sessionSecret
     * @return string
     */
    public static function generate($userID, $sessionSecret) {
        return base64_encode ($userID . ':' . $sessionSecret);
    }

    /**
     * Checks if a given token is valid
     *
     * @param $tokenString
     * @return bool
     */
    private static function isValidTokenString($tokenString) {
        if($tokenString === null)
            return false;

        // Decode the token
        $decoded = base64_decode($tokenString);

        // Could not decode token
        if($decoded === false)
            return false;

        // Decoded token does not match regex
        if(!preg_match(self::VALIDITY_REGEX, $decoded))
            return false;

        return true;
    }

    /**
     * Reads a token and returns the variables
     *
     * @param $tokenString
     * @return array|null
     */
    public static function readToken($tokenString) {
        // Token is not of valid structure
        if(!self::isValidTokenString($tokenString))
            return null;

        // Decode the token
        $decoded = base64_decode($tokenString);

        // Explode token
        $exploded = explode(':', $decoded);

        return [
            'user_id'           => $exploded[0],
            'session_secret'    => $exploded[1]
        ];
    }
}