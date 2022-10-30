<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureAPIRequestsAreStateful
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse)  $next
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
