<?php

namespace App\Actions\Web\Auth;

use Auth;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Helpers\SignInRateLimiter;

class AttemptToAuthenticate
{
    /**
     * The login rate limiter instance.
     *
     * @var SignInRateLimiter
     */
    protected SignInRateLimiter $limiter;

    /**
     * Create a new controller instance.
     *
     * @param SignInRateLimiter $limiter
     *
     * @return void
     */
    public function __construct(SignInRateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param callable $next
     *
     * @return mixed
     * @throws ValidationException
     */
    public function handle(Request $request, callable $next): mixed
    {
        if (Auth::attempt(
            $request->only('email', 'password'),
            $request->filled('remember'))
        ) {
            return $next($request);
        }

        $this->throwFailedAuthenticationException($request);
    }

    /**
     * Attempt to authenticate using a custom callback.
     *
     * @param Request $request
     * @param callable $next
     *
     * @return mixed
     * @throws ValidationException
     */
    protected function handleUsingCustomCallback(Request $request, callable $next): mixed
    {
        $user = Auth::user();

        if (!$user) {
            $this->fireFailedEvent($request);

            return $this->throwFailedAuthenticationException($request);
        }

        Auth::login($user, $request->filled('remember'));

        return $next($request);
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param Request $request
     *
     * @return void
     * @throws ValidationException
     */
    protected function throwFailedAuthenticationException(Request $request)
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param Request $request
     * @return void
     */
    protected function fireFailedEvent(Request $request)
    {
        event(new Failed('web', null, [
            'email' => $request->email,
            'password' => $request->password,
        ]));
    }
}
