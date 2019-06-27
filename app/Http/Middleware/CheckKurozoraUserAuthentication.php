<?php

namespace App\Http\Middleware;

use App\Helpers\JSONResult;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;
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
            return JSONResult::error('Unable to read authentication token.');

        // Fetch the variables
        $givenSecret = $readToken['session_secret'];
        $givenUserID = $readToken['user_id'];

        // Check authentication
        $sessionAuthenticate = User::authenticateSession($givenUserID, $givenSecret);

        if($sessionAuthenticate === false)
            return JSONResult::error(JSONResult::ERROR_SESSION_REJECTED);

        // Add to request
        $request->request->add(['user_id' => (int) $givenUserID]);
        $request->request->add(['session_secret' => $givenSecret]);
        $request->request->add(['session_id' => $sessionAuthenticate]);

        // Log the user in
        Auth::loginUsingId($request['user_id']);

        return $next($request);
    }
}
