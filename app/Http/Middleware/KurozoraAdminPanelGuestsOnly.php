<?php

namespace App\Http\Middleware;

use Closure;

class KurozoraAdminPanelGuestsOnly
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

        // User is a guest
        if($sessionUserID == null && $sessionSecret == null) {
            return $next($request);
        }

        // User is not a guest
        return redirect()->route('admin_panel.dashboard');
    }
}
