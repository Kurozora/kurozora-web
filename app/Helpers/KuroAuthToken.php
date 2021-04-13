<?php

namespace App\Helpers;

class KuroAuthToken {
    const VALIDITY_REGEX = '/^[0-9]+:[^:]+$/';

    /**
     * Generates a Kuro Auth Token based on the given details
     *
     * @param int $userID
     * @param string $sessionSecret
     * @return string
     */
    public static function generate($userID, $sessionSecret)
    {
        return base64_encode($userID . ':' . $sessionSecret);
    }

    /**
     * Checks if an auth token has a valid format.
     *
     * @param string $token
     * @return bool
     */
    private static function tokenHasValidFormat($token)
    {
        if ($token === null)
            return false;

        // Decode the token
        $decoded = base64_decode($token);

        // Could not decode token
        if ($decoded === false)
            return false;

        // Decoded token does not match regex
        if (!preg_match(self::VALIDITY_REGEX, $decoded))
            return false;

        return true;
    }

    /**
     * Reads an auth token and returns the parts.
     *
     * @param string $token
     * @return array|null
     */
    public static function readToken($token)
    {
        // Token is not of valid structure
        if (!self::tokenHasValidFormat($token))
            return null;

        // Decode the token
        $decoded = base64_decode($token);

        // Explode token
        $exploded = explode(':', $decoded);

        return [
            'user_id'           => (int) $exploded[0],
            'session_secret'    => $exploded[1]
        ];
    }
}
