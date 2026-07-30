<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class UserIsProOrSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        # Allow only if the user is admin or id matches
        $user = auth()->user();

        if ($user?->is_pro || $user?->is_subscribed) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            throw new AuthorizationException(__('This feature requires an active Kurozora+ subscription or Pro.'));
        }

        return to_route('kurozora-plus');
    }
}
