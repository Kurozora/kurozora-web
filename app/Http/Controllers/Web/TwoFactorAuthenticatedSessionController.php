<?php

namespace App\Http\Controllers\Web;

use App\Actions\Web\Auth\PrepareAuthenticatedSession;
use App\Actions\Web\Auth\RedirectIfHasLocalLibrary;
use App\Events\RecoveryCodeReplaced;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\TwoFactorSignInRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pipeline\Pipeline;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Psr\SimpleCache\InvalidArgumentException;

class TwoFactorAuthenticatedSessionController extends Controller
{
    /**
     * Show the two-factor authentication challenge view.
     *
     * @param TwoFactorSignInRequest $request
     * @return Application|Factory|View
     */
    public function create(TwoFactorSignInRequest $request): Application|Factory|View
    {
        if (!$request->hasChallengedUser()) {
            throw new HttpResponseException(redirect()->route('sign-in'));
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Attempt to authenticate a new session using the two-factor authentication code.
     *
     * @param TwoFactorSignInRequest $request
     * @return RedirectResponse
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     * @throws InvalidArgumentException
     */
    public function store(TwoFactorSignInRequest $request): RedirectResponse
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);

            event(new RecoveryCodeReplaced($user, $code));
        } else if (!$request->hasValidCode()) {
            return redirect()->route('two-factor.sign-in')->withErrors(['code' => __('The provided two-factor authentication code was invalid.')]);
        }

        auth()->login($user, $request->remember());

        return $this->signInPipeline($request)->then(function () {
            return redirect()->intended();
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param TwoFactorSignInRequest $request
     * @return Pipeline
     */
    protected function signInPipeline(TwoFactorSignInRequest $request): Pipeline
    {
        return (new Pipeline(app()))->send($request)->through(array_filter([
            PrepareAuthenticatedSession::class,
            RedirectIfHasLocalLibrary::class,
        ]));
    }
}
