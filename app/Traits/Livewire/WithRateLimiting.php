<?php

namespace App\Traits\Livewire;

use Illuminate\Validation\ValidationException;
use RateLimiter;

trait WithRateLimiting
{
    /**
     * Clear the rate limiter for the current request.
     *
     * @param null|string $method
     * @param null|string $component
     *
     * @return void
     */
    protected function clearRateLimiter(string $method = null, string $component = null): void
    {
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1]['function'];

        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);

        RateLimiter::clear($key);
    }

    /**
     * Get the rate limit key for the current request.
     *
     * @param null|string $method
     * @param null|string $component
     *
     * @return string
     */
    protected function getRateLimitKey(?string $method, string $component = null): string
    {
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1]['function'];

        $component ??= static::class;

        return 'livewire-rate-limiter:' . sha1($component . '|' . $method . '|' . request()->ip());
    }

    /**
     * Hit the rate limiter for the current request.
     *
     * @param null|string $method
     * @param int         $decaySeconds
     * @param null|string $component
     *
     * @return void
     */
    protected function hitRateLimiter(string $method = null, int $decaySeconds = 60, string $component = null): void
    {
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1]['function'];

        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);

        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * Get the number of seconds until the rate limit is available.
     *
     * @param null|string $method
     * @param null|string $component
     *
     * @return int
     */
    protected function rateLimitAvailableIn(string $method = null, string $component = null): int
    {
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1]['function'];

        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);

        return RateLimiter::availableIn($key);
    }

    /**
     * Rate limit the current request.
     *
     * @param int         $maxAttempts
     * @param int         $decaySeconds
     * @param null|string $method
     * @param null|string $component
     *
     * @return void
     */
    protected function rateLimit(int $maxAttempts, int $decaySeconds = 60, string $method = null, string $component = null): void
    {
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1]['function'];

        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $secondsUntilAvailable = RateLimiter::availableIn($key);

            $this->resetErrorBag();
            throw ValidationException::withMessages([
                'rate_limit' => __('You are being rate limited. Please try again in :seconds seconds.', ['seconds' => $secondsUntilAvailable]),
                'seconds_until_available' => $secondsUntilAvailable,
            ])->errorBag($method);
        }
        $this->hitRateLimiter($method, $decaySeconds, $component);
    }
}
