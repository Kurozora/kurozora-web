<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TVRating
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
            $tvRating = auth()->user()->tv_rating;
        } else if ($request->hasHeader('X-TV-Rating')) {
            $tvRating = $request->header('X-TV-Rating');
        } else if (session()->has('tv_rating')) {
            $tvRating = session('tv_rating');
        } else {
            $tvRating = 4;
        }

        // Set TVRating in config
        config()->set('app.tv_rating', $tvRating);

        // Continue request
        return $next($request);
    }
}
