<?php

namespace App\Actions\Web\Auth;

use App\Helpers\SignInRateLimiter;
use App\Models\Session;
use Browser;
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
        $regenerated = $request->session()->regenerate();
        $request->session()->save();

        $session = Session::firstWhere('id', $request->session()->getId());
        $browser = Browser::detect();

        auth()->user()->createSessionAttributes($session, [
            'platform'          => $browser->platformFamily(),
            'platform_version'  => $browser->platformVersion(),
            'device_vendor'     => $browser->deviceFamily(),
            'device_model'      => $browser->deviceModel(),
        ], $regenerated);

        $this->limiter->clear($request);

        return $next($request);
    }
}
