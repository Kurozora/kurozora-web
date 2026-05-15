<?php

namespace App\Http\Middleware;

use App\Models\APIClientToken;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAPIClient
{
    /**
     * The validity cache TTL for an API client token, in seconds.
     */
    const int VALIDITY_CACHE_TTL = 300;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = parse_user_agent($request->userAgent());

        if ($request->routeIs('api') || $request->routeIs('api.index')) {
            return $next($request);
        }

        $request->headers->set('Accept', 'application/json');

        $jwt = $request->header('X-API-Key');

        if (!$jwt) {
            // Throw authorization error message
            throw new AuthenticationException('X-API-Key header missing: ' . route('kb.generating-developer-tokens'));
        }

        // TODO: - Replace with JWT validation
        if ($this->tokenIsValid($userAgent['bundle'], $jwt)) {
            return $next($request);
        } else {
            throw new AuthenticationException('The request wasn’t accepted due to an issue with the credentials.');
        }

        // TODO: - Finish implementing this after the above initial version is released
        try {
            // Decode only the JWT header to extract `kid`
            $decodedHeader = JWT::jsonDecode(JWT::urlsafeB64Decode(explode('.', $jwt)[0]));

            if (!isset($decodedHeader->kid)) {
                throw new AuthenticationException('Invalid API key: ' . '1');
            }

            $kid = $decodedHeader->kid;

            // Fetch the `APIClientToken` for this `kid`
            $apiClient = APIClientToken::where('key_id', $kid)->first();

            if (!$apiClient) {
                throw new AuthenticationException('Invalid API key: ' . '2');
            }

            // Verify JWT with stored public key
            $decodedToken = JWT::decode($jwt, new Key($apiClient->public_key, 'ES256'));

            // Validate claims
            if ($decodedToken->iss !== $apiClient->user_id) {
                throw new AuthenticationException('Invalid API key: ' . '3');
            }

            if ($decodedToken->sub !== $apiClient->identifier) {
                throw new AuthenticationException('Invalid API key: ' . '4');
            }

            if ($decodedToken->exp < time()) {
                throw new AuthenticationException('Invalid API key: ' . '5');
            }

            if ($decodedToken->aud !== route('home')) {
                throw new AuthenticationException('Invalid API key: ' . '6');
            }

            if (isset($decodedToken->origins) && in_array($request->headers->get('origin'), $decodedToken->origins)) {
                throw new AuthenticationException('Invalid API key: ' . '7');
            }

            return $next($request);
        } catch (Exception) {
            throw new AuthenticationException('Invalid API key: ' . '8');
        }
    }

    /**
     * Returns whether the given identifier and JWT pair matches a known API client token.
     *
     * @param string $identifier
     * @param string $jwt
     *
     * @return bool
     */
    private function tokenIsValid(string $identifier, string $jwt): bool
    {
        $cacheKey = 'api-client-token:' . sha1($identifier . '|' . $jwt);

        if (Cache::get($cacheKey) === true) {
            return true;
        }

        $exists = APIClientToken::where([
            ['identifier', '=', $identifier],
            ['token', '=', $jwt],
        ])->exists();

        if ($exists) {
            Cache::put($cacheKey, true, self::VALIDITY_CACHE_TTL);
        }

        return $exists;
    }
}
