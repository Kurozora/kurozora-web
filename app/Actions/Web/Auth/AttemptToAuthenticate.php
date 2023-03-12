<?php

namespace App\Actions\Web\Auth;

use App\Helpers\SignInRateLimiter;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttemptToAuthenticate
{
    /**
     * The guard implementation.
     *
     * @var StatefulGuard
     */
    protected StatefulGuard $guard;

    /**
     * The login rate limiter instance.
     *
     * @var SignInRateLimiter
     */
    protected SignInRateLimiter $limiter;

    /**
     * Create a new controller instance.
     *
     * @param  StatefulGuard  $guard
     * @param  SignInRateLimiter  $limiter
     *
     * @return void
     */
    public function __construct(StatefulGuard $guard, SignInRateLimiter $limiter)
    {
        $this->guard = $guard;
        $this->limiter = $limiter;
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle(Request $request, callable $next): mixed
    {
        if ($this->guard->attempt(
            $request->only('email', 'password'),
            $request->filled('remember'))
        ) {
            return $next($request);
        }
        $this->throwFailedAuthenticationException($request);
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param Request $request
     * @return void
     *
     * @throws ValidationException
     */
    protected function throwFailedAuthenticationException(Request $request): void
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            'email' => [__('auth.failed')],
        ]);
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param Request $request
     * @return void
     */
    protected function fireFailedEvent(Request $request): void
    {
        event(new Failed('web', null, [
            'email' => $request->email,
            'password' => $request->password,
        ]));
    }
}
