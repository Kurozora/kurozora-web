<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AdminPanelController;
use App\User;
use Closure;

class CheckKurozoraAdminPanelAuthentication
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
        // Get stored session variables
        $sessionUserID = session('user_id');
        $sessionSecret = session('session_secret');

        // Session variables weren't set
        if($sessionUserID == null || $sessionSecret == null) {
            return AdminPanelController::toLoginPage($request, 'Please login first.');
        }

        // Check authentication
        $sessionAuthenticate = User::authenticateSession($sessionUserID, $sessionSecret);

        if($sessionAuthenticate === false) {
            $request->session()->forget('user_id');
            $request->session()->forget('session_secret');

            return AdminPanelController::toLoginPage($request, 'Your session has expired.');
        }

        // Add to request
        $request->user_id = (int) $sessionUserID;
        $request->session_secret = $sessionSecret;
        $request->session_id = $sessionAuthenticate;
        $request->user = User::find($request->user_id);

        return $next($request);
    }
}
