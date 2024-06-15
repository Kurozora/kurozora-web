<?php

namespace App\Http\Controllers\Web;

use App\Actions\Web\Auth\DisableTwoFactorAuthentication;
use App\Actions\Web\Auth\EnableTwoFactorAuthentication;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two-factor authentication for the user.
     *
     * @param Request $request
     * @param EnableTwoFactorAuthentication $enable
     * @return RedirectResponse
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable): RedirectResponse
    {
        $enable($request->user());

        return back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Disable two-factor authentication for the user.
     *
     * @param Request $request
     * @param DisableTwoFactorAuthentication $disable
     * @return RedirectResponse
     */
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable): RedirectResponse
    {
        $disable($request->user());

        return back()->with('status', 'two-factor-authentication-disabled');
    }
}
