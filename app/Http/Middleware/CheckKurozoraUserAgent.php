<?php

namespace App\Http\Middleware;

use Closure;

class CheckKurozoraUserAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if the user agent is sufficient
        $userAgent = $request->server('HTTP_USER_AGENT');

        if(!str_contains($userAgent, config('app.ios_bundle_id'))) {
            return abort(403, 'Unauthorized action.');
        }

        // Continue with the request
        return $next($request);
    }
}
