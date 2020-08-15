<?php

namespace App\Http\Middleware;

use App\Session;
use Closure;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\KuroAuthToken;
use Laravel\Nova\Exceptions\AuthenticationException;

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
     * @param null|string $parameter
     * @return JsonResponse|mixed
     * @throws AuthorizationException
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

            throw new AuthorizationException('The request wasn’t accepted due to an issue with the kuro-auth token or because it’s using incorrect authentication.');
        }

        // Read the authentication token
        $token = KuroAuthToken::readToken($rawToken);

        if($token === null)
            throw new AuthorizationException('The request wasn’t accepted due to an issue with the kuro-auth token or because it’s using incorrect authentication.');

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
     * @return JsonResponse|mixed
     * @throws AuthorizationException
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

        // Check whether the session exists
        if($session === null)
            throw new AuthorizationException('The request wasn’t accepted due to an expired kuro-auth token.');

        if($session->isExpired()) {
            $session->delete();

            throw new AuthorizationException('The request wasn’t accepted due to an expired kuro-auth token.');
        }

        // Update the session's fields if necessary
        $this->updateSession($session);

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
     * Updates the session's fields if necessary.
     *
     * @param Session $session
     */
    private function updateSession($session)
    {
        // Extend the session's lifetime when at least 1 day has passed
        if($session->expires_at->startOfDay() < now()->addDays(Session::VALID_FOR_DAYS)->startOfDay())
        {
            $session->expires_at = now()->addDays(Session::VALID_FOR_DAYS);
        }

        // Update the last validated date
        $session->last_validated_at = now();

        $session->save();
    }
}
