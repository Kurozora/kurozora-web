<?php

namespace App\Providers\SocialiteProviders;

use DateInterval;
use DateMalformedIntervalStringException;
use Firebase\JWT\JWK;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Two\InvalidStateException;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use SocialiteProviders\Apple\AppleSignerInMemory;
use SocialiteProviders\Apple\Provider;

class AppleProvider extends Provider
{
    /**
     * The cache key used for the Apple JWKSet.
     */
    private const string JWKSET_CACHE_KEY = 'socialite:Apple-JWKSet';

    /**
     * The cache TTL for the Apple JWKSet, in seconds.
     */
    private const int JWKSET_CACHE_TTL = 24 * 60 * 60;

    /**
     * Verifies the given Apple identity JWT, refreshing the cached JWKSet once if the signing key is unknown.
     *
     * @param string $jwt
     *
     * @return bool
     * @throws DateMalformedIntervalStringException
     * @see https://appleid.apple.com/auth/keys
     */
    public function checkToken($jwt): bool
    {
        $token = $this->getJwtConfig()->parser()->parse($jwt);
        $kid = $token->headers()->get('kid');

        $publicKeys = JWK::parseKeySet($this->fetchJWKSet());

        if (!isset($publicKeys[$kid])) {
            Cache::forget(self::JWKSET_CACHE_KEY);
            $publicKeys = JWK::parseKeySet($this->fetchJWKSet());
        }

        if (!isset($publicKeys[$kid])) {
            throw new InvalidStateException('Invalid JWT Signature');
        }

        $publicKey = openssl_pkey_get_details($publicKeys[$kid]->getKeyMaterial());
        $constraints = [
            new SignedWith(new Sha256, AppleSignerInMemory::plainText($publicKey['key'])),
            new IssuedBy(self::URL),
            new LooseValidAt(SystemClock::fromSystemTimezone(), new DateInterval($this->getConfig('jwt_issued_time_leeway', 'PT3S'))),
        ];

        try {
            $this->jwtConfig->validator()->assert($token, ...$constraints);

            return true;
        } catch (RequiredConstraintsViolated $exception) {
            throw new InvalidStateException($exception->getMessage());
        }
    }

    /**
     * Retrieves Apple's published JWKSet from cache, fetching from the network on a miss.
     *
     * @return array
     */
    private function fetchJWKSet(): array
    {
        return Cache::remember(self::JWKSET_CACHE_KEY, self::JWKSET_CACHE_TTL, function () {
            $response = (new Client)->get(self::URL . '/auth/keys');

            return json_decode((string) $response->getBody(), true);
        });
    }
}
