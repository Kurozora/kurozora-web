<?php

namespace App\Http\Controllers\Web\Nova;

use App\Actions\Web\Auth\AttemptToAuthenticate;
use App\Actions\Web\Auth\PrepareAuthenticatedSession;
use App\Actions\Web\Auth\RedirectIfTwoFactorAuthenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Http\Controllers\LoginController;
use Symfony\Component\HttpFoundation\Response;

class SignInController extends LoginController
{
    /**
     * Handle a sign in request to the application.
     *
     * @param Request $request
     * @return JsonResponse|Response|RedirectResponse
     *
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse|Response|RedirectResponse
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the sign in attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        return $this->signInPipeline($request)->then(function () {
            return redirect()->intended($this->redirectPath());
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param Request $request
     * @return Pipeline
     */
    protected function signInPipeline(Request $request): Pipeline
    {
        return (new Pipeline(app()))->send($request)->through(array_filter([
            RedirectIfTwoFactorAuthenticatable::class,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]));
    }
}
