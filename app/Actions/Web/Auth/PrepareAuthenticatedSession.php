<?php

namespace App\Actions\Web\Auth;

use App\Helpers\SignInRateLimiter;
use Illuminate\Http\Request;

class PrepareAuthenticatedSession
{
    /**
     * The login rate limiter instance.
     *
     * @var SignInRateLimiter
     */
    protected SignInRateLimiter $limiter;

    /**
     * Create a new class instance.
     *
     * @param SignInRateLimiter $limiter
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
     * @return mixed
     */
    public function handle(Request $request, callable $next): mixed
    {
        $request->session()->regenerate();

        $this->limiter->clear($request);

        return $next($request);
    }
}
