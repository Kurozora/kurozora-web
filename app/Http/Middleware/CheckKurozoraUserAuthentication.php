<?php

namespace App\Http\Middleware;

use App\Helpers\JSONResult;
use App\User;
use Closure;
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
        // Validate parameters
        $validator = Validator::make($request->all(), [
            'session_secret'    => 'bail|required',
            'user_id'           => 'bail|required|numeric'
        ]);

        // Fetch the variables
        $givenSecret = $request->input('session_secret');
        $givenUserID = $request->input('user_id');

        // Check authentication
        $sessionAuthenticate = User::authenticateSession($givenUserID, $givenSecret);

        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        if($sessionAuthenticate === false)
            (new JSONResult())->setError(JSONResult::ERROR_SESSION_REJECTED)->show();

        // Add to request
        $request->user_id = (int) $givenUserID;
        $request->session_secret = $givenSecret;
        $request->session_id = $sessionAuthenticate;

        return $next($request);
    }
}
