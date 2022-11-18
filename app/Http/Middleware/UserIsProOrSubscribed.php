<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserIsProOrSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        # Allow only if the user is admin or id matches
        $user = auth()->user();

        if ($user?->is_pro || $user?->is_subscribed) {
            return $next($request);
        }

        return to_route('profile.settings');
    }
}
