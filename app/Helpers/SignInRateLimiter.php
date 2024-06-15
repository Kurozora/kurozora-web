<?php

namespace App\Helpers;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class SignInRateLimiter
{
    /**
     * The login rate limiter instance.
     *
     * @var RateLimiter
     */
    protected RateLimiter $limiter;

    /**
     * Create a new login rate limiter instance.
     *
     * @param RateLimiter $limiter
     *
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Get the number of attempts for the given key.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function attempts(Request $request): mixed
    {
        return $this->limiter->attempts($this->throttleKey($request));
    }

    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function tooManyAttempts(Request $request): bool
    {
        return $this->limiter->tooManyAttempts($this->throttleKey($request), 5);
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param Request $request
     *
     * @return void
     */
    public function increment(Request $request): void
    {
        $this->limiter->hit($this->throttleKey($request), 60);
    }

    /**
     * Determine the number of seconds until logging in is available again.
     *
     * @param Request $request
     *
     * @return int
     */
    public function availableIn(Request $request): int
    {
        return $this->limiter->availableIn($this->throttleKey($request));
    }

    /**
     * Clear the login locks for the given user credentials.
     *
     * @param Request $request
     *
     * @return void
     */
    public function clear(Request $request): void
    {
        $this->limiter->clear($this->throttleKey($request));
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function throttleKey(Request $request): string
    {
        $email = $request->input('email');

        if ($user = $request->challengedUser()) {
            $email = $user->email;
        }

        return str(str($email)->lower() . '|' . $request->ip())->transliterate();
    }
}
