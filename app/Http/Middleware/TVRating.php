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
        $user = auth()->user();

        // Check header request and determine TV rating
        if ($user !== null) {
            $tvRating = $user->tv_rating;
        } else if ($request->hasHeader('X-TV-Rating')) {
            $tvRating = $request->header('X-TV-Rating');
        } else if (session()->has('tv_rating')) {
            $tvRating = session('tv_rating');
        } else {
            $tvRating = 4;
        }

        // Set TV rating on the request
        $request->attributes->set('tvRating', $tvRating);

        // Continue request
        return $next($request);
    }
}
