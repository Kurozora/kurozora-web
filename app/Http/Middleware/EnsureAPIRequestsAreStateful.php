<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class EnsureAPIRequestsAreStateful
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Bearer is empty or user is empty
        $user = Auth::guard('sanctum')->user();

        if ($user) {
            // Set user if bearer is valid
            Auth::setUser($user);
        }

        return $next($request);
    }
}
