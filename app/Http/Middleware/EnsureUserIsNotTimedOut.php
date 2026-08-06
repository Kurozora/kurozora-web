<?php

namespace App\Http\Middleware;

use App\Exceptions\UserTimedOutException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotTimedOut
{
    /**
     * Handle an incoming request.
     *
     * @param Request                      $request
     * @param Closure(Request): (Response) $next
     *
     * @return Response
     * @throws UserTimedOutException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $activeTimeout = $user->timeouts()
            ->active()
            ->latest('id')
            ->first();

        if ($activeTimeout !== null) {
            throw new UserTimedOutException($activeTimeout);
        }

        return $next($request);
    }
}
