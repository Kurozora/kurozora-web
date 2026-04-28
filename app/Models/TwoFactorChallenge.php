<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TwoFactorChallenge extends KModel
{
    use MassPrunable;

    // Table name
    const string TABLE_NAME = 'two_factor_challenges';
    protected $table = self::TABLE_NAME;

    /**
     * The minutes a challenge token remains valid for after issue.
     */
    const int TTL_MINUTES = 5;

    /**
     * The maximum number of invalid OTP submissions allowed before the challenge token is invalidated.
     */
    const int MAX_ATTEMPTS = 5;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'platform_data' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * The user the challenge belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        return self::query()->where('expires_at', '<', Carbon::now());
    }

    /**
     * Issue a new challenge for the given user and return the plaintext token.
     *
     * The plaintext token is shown to the client once and never persisted.
     *
     * @param User  $user
     * @param array $platformData
     *
     * @return string
     */
    public static function issue(User $user, array $platformData = []): string
    {
        $plainToken = Str::random(64);

        self::create([
            'user_id' => $user->uuid,
            'token_hash' => hash('sha256', $plainToken),
            'attempts_used' => 0,
            'platform_data' => $platformData,
            'expires_at' => Carbon::now()->addMinutes(self::TTL_MINUTES),
        ]);

        return $plainToken;
    }

    /**
     * Find a valid challenge by its plaintext token.
     *
     * @param string $plainToken
     *
     * @return self|null
     */
    public static function findValid(string $plainToken): ?self
    {
        return self::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    /**
     * Determine whether this challenge has reached the maximum number of invalid
     * OTP submissions and is no longer redeemable.
     *
     * @return bool
     */
    public function isExhausted(): bool
    {
        return $this->attempts_used >= self::MAX_ATTEMPTS;
    }

    /**
     * Atomically increment the failed attempt counter.
     *
     * @return void
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts_used');
    }

    /**
     * Invalidate this challenge by setting its expiry to now.
     *
     * @return void
     */
    public function invalidate(): void
    {
        $this->expires_at = Carbon::now();
        $this->save();
    }
}
