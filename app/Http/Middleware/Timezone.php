<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Timezone
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Check header request and determine localization
        if (auth()->check()) {
            $tvRating = auth()->user()->timezone;
        } else if ($request->hasHeader('X-Timezone')) {
            $tvRating = $request->header('X-Timezone');
        } else if (session()->has('timezone')) {
            $tvRating = session('timezone');
        } else {
            $tvRating = 4;
        }

        // Set TVRating in config
        config()->set('app.timezone', $tvRating);

        // Continue request
        return $next($request);
    }
}
