<?php

namespace App\Helpers;

use CoderCat\JWKToPEM\JWKConverter;
use Exception;
use Illuminate\Support\Facades\Cache;

class AppleAuthKeys
{
    private const KEYS_ENDPOINT_URL = 'https://appleid.apple.com/auth/keys';
    private const KEYS_CACHE_KEY = 'apple_auth_keys';
    private const CACHE_FOR_SECONDS = 86400;

    private function __construct() { }

    /**
     * Returns an array of (string) PEM public keys.
     *
     * @return string[]
     */
    static function get()
    {
        return Cache::remember(self::KEYS_CACHE_KEY, self::CACHE_FOR_SECONDS, function() {
            $jwk = json_decode(file_get_contents(self::KEYS_ENDPOINT_URL));

            // Convert apple keys to PEM
            $jwkConverter = new JWKConverter();

            $keys = [];

            foreach ($jwk->keys as $appleKey) {
                try {
                    $keys[] = $jwkConverter->toPem((array)$appleKey);
                } catch (Exception $e) {
                    return [];
                }
            }

            return $keys;
        });
    }

    /**
     * Returns whether the Apple auth keys are cached.
     *
     * @return bool
     */
    static function areCached()
    {
        return Cache::has(self::KEYS_CACHE_KEY);
    }
}
