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

        // Loop through bundle IDs
        foreach(config('app.ios_bundle_id') as $bundleID) {
            if(str_contains($userAgent, $bundleID))
                return next($request);
        }

        return abort(403, 'Unauthorized bearer.');
    }
}
