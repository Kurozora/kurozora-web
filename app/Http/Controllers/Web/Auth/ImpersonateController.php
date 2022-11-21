<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Nova\Contracts\ImpersonatesUsers;

class ImpersonateController extends Controller
{
    /**
     * Stop impersonating a user.
     *
     * @param Request $request
     * @param ImpersonatesUsers $impersonator
     * @return RedirectResponse
     */
    function stopImpersonating(Request $request, ImpersonatesUsers $impersonator): RedirectResponse
    {
        if ($impersonator->impersonating($request)) {
            $impersonator->stopImpersonating($request, Auth::guard(), User::class);
        }

        return to_route('nova.pages.home');
    }
}
