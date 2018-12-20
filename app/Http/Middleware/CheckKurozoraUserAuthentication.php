<?php

namespace App\Http\Middleware;

use App\Helpers\JSONResult;
use App\User;
use Closure;
use KuroAuthToken;
use Validator;

/*
 * This middleware checks the Kurozora user's authentication details before
 * going through with the request
 */
class CheckKurozoraUserAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get kuro auth token from header
        $givenAuthToken = $request->header('kuro-auth');

        // Read the token
        $readToken = KuroAuthToken::readToken($givenAuthToken);

        // Unable to read token
        if($readToken === null)
            (new JSONResult())->setError('Unable to read authentication token.')->show();

        // Fetch the variables
        $givenSecret = $readToken['session_secret'];
        $givenUserID = $readToken['user_id'];

        // Check authentication
        $sessionAuthenticate = User::authenticateSession($givenUserID, $givenSecret);

        if($sessionAuthenticate === false)
            (new JSONResult())->setError(JSONResult::ERROR_SESSION_REJECTED)->show();

        // Add to request
        $request->user_id = (int) $givenUserID;
        $request->session_secret = $givenSecret;
        $request->session_id = $sessionAuthenticate;

        return $next($request);
    }
}
