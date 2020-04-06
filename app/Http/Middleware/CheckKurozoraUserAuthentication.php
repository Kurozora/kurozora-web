<?php

namespace App\Http\Middleware;

use App\Helpers\JSONResult;
use App\Session;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\KuroAuthToken;

/*
 * This middleware checks the Kurozora user's authentication details before
 * going through with the request
 */
class CheckKurozoraUserAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param null|string $parameter
     * @return mixed
     * @throws Exception
     */
    public function handle($request, Closure $next, $parameter = null)
    {
        // Check whether parameter value is valid
        if(!in_array($parameter, [null, 'optional']))
            throw new Exception('Middleware parameter value "' . $parameter . '" is not valid.');

        // Get kuro auth token from header
        $rawToken = $request->header('kuro-auth');

        // Header is invalid
        if(!is_string($rawToken)) {
            // Continue with the request if authentication is optional
            if($parameter === 'optional')
                return $next($request);

            return JSONResult::error('Authentication token is not in a correct format.');
        }

        // Read the authentication token
        $token = KuroAuthToken::readToken($rawToken);

        if($token === null)
            return JSONResult::error('Unable to read authentication token.');

        // Fetch the variables
        $secret = $token['session_secret'];
        $userID = $token['user_id'];

        return $this->authenticate($userID, $secret, $next, $request);
    }

    /**
     * Checks the given authentication details.
     *
     * @param int $userID
     * @param string $secret
     * @param Closure $next
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws Exception
     */
    private function authenticate($userID, $secret, Closure $next, $request)
    {
        // Find the session
        /** @var Session $session */
        $session = Session::where([
            ['user_id', '=', $userID],
            ['secret',  '=', $secret]
        ])->first();

        // Check whether the session has expired
        if($session === null)
            return $this->sessionExpiredResponse();

        if($session->isExpired()) {
            $session->delete();

            return $this->sessionExpiredResponse();
        }

        // Log the user in
        $request->request->add([
            'user_id'           => (int) $userID,
            'session_secret'    => $secret,
            'session_id'        => $session->id
        ]);

        Auth::loginUsingId($request['user_id']);

        return $next($request);
    }

    /**
     * Returns the response for expired sessions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function sessionExpiredResponse()
    {
        return JSONResult::error('Your session has expired.', [
            'status_code' => 401
        ]);
    }
}
