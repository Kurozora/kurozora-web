<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

/*
 * This middleware checks the Kurozora user's authentication details before
 * going through with the request
 */
class CheckKurozoraUserAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param ?string $parameter
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next, ?string $parameter = null): mixed
    {
        // Check whether parameter value is valid
        if (!in_array($parameter, [null, 'optional'])) {
            throw new Exception('Middleware parameter value "' . $parameter . '" is not valid.');
        }

        // Continue with the request if the user exists
        if ($request->user()) {
            return $next($request);
        }

        // Continue with the request if authentication is optional
        if ($parameter === 'optional') {
            return $next($request);
        }

        // Throw an exception when authentication required but missing
        throw new AuthorizationException(__('The request wasn’t accepted due to an issue with the bearer token or because it’s using incorrect authentication.'));
    }
}
