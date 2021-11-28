<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\TwoFactorSignInRequest;
use Auth;
use Browser;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;

class TwoFactorAuthenticatedSessionController extends Controller
{
    /**
     * Show the two factor authentication challenge view.
     *
     * @return Application|Factory|View
     */
    public function create(): Application|Factory|View
    {
        return view('auth.two-factor-challenge');
    }

    /**
     * Attempt to authenticate a new session using the two factor authentication code.
     *
     * @param TwoFactorSignInRequest $request
     * @return RedirectResponse
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function store(TwoFactorSignInRequest $request): RedirectResponse
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);
        } else if (!$request->hasValidCode()) {
            return back()->withErrors([
                'email' => __('The provided two factor authentication code was invalid.'),
            ]);
        }

        Auth::login($user, $request->remember());

        $this->prepareAuthenticatedSession();

        return redirect()->intended();
    }

    /**
     * Prepares the authenticated session for the newly authenticated user.
     */
    protected function prepareAuthenticatedSession()
    {
        $browser = Browser::detect();

        Auth::user()->createSessionAttributes([
            'platform'          => $browser->platformFamily(),
            'platform_version'  => $browser->platformVersion(),
            'device_vendor'     => $browser->deviceFamily(),
            'device_model'      => $browser->deviceModel(),
        ]);
    }
}
