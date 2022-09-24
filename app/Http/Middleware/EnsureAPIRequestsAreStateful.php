<?php

namespace App\Http\Middleware;

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
        $user = auth()->guard('sanctum')->user();

        if ($user) {
            // Set user if bearer is valid
            auth()->setUser($user);
        }

        return $next($request);
    }
}
