<?php

namespace App\Http\Middleware;

use App\Helpers\JSONResult;
use App\User;
use Closure;

class CheckKurozoraUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $minimumRole)
    {
        // Check if the user has the appropriate minimum role
        if(!User::where([
            ['id',      '=',    $request->user_id],
            ['role',    '>=',   $minimumRole]
        ])->exists()) {
            (new JSONResult())
                ->setError('You must at least have the rank ' . User::getStringFromRole($minimumRole) . ' to do this.')
                ->show();
        }

        return $next($request);
    }
}
