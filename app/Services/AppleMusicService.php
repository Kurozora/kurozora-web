<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Throwable;

class AppleMusicService
{
    /**
     * The cache key for the MusicKit developer token.
     *
     * @var string
     */
    protected string $cacheKey = 'apple-music.developer-token';

    /**
     * Returns the MusicKit developer token and its expiry timestamp.
     *
     * @return array{token: ?string, expiresAt: ?int}
     */
    public function developerToken(): array
    {
        if (blank(config('services.apple.music.private_key')) || blank(config('services.apple.music.key_id')) || blank(config('services.apple.music.team_id'))) {
            return ['token' => null, 'expiresAt' => null];
        }

        try {
            $ttl = (int) config('services.apple.music.token_ttl');

            return Cache::remember($this->cacheKey, intdiv($ttl, 2), fn(): array => $this->mint($ttl));
        } catch (Throwable $exception) {
            report($exception);

            return ['token' => null, 'expiresAt' => null];
        }
    }

    /**
     * Mints a fresh developer token valid for the given lifetime in seconds.
     *
     * @param int $ttl
     *
     * @return array{token: string, expiresAt: int}
     */
    protected function mint(int $ttl): array
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + $ttl;

        $token = JWT::encode([
            'iss' => config('services.apple.music.team_id'),
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ], config('services.apple.music.private_key'), 'ES256', config('services.apple.music.key_id'));

        return ['token' => $token, 'expiresAt' => $expiresAt];
    }
}
