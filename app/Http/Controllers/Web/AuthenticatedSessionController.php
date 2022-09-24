<?php

namespace App\Http\Controllers\Web;

use App\Actions\Web\Auth\AttemptToAuthenticate;
use App\Actions\Web\Auth\PrepareAuthenticatedSession;
use App\Actions\Web\Auth\RedirectIfTwoFactorAuthenticatable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SignInRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Redirector;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the sign in view.
     *
     * @return Application|Factory|View
     */
    public function create(): Application|Factory|View
    {
        return view('auth.sign-in');
    }

    /**
     * Attempt to authenticate a new session.
     *
     * @param SignInRequest $request
     * @return RedirectResponse
     */
    public function store(SignInRequest $request): RedirectResponse
    {
        return $this->signInPipeline($request)->then(function () {
            return redirect()->intended();
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param SignInRequest $request
     * @return Pipeline
     */
    protected function signInPipeline(SignInRequest $request): Pipeline
    {
        return (new Pipeline(app()))->send($request)->through(array_filter([
            RedirectIfTwoFactorAuthenticatable::class,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]));
    }

    /**
     * Destroy an authenticated session.
     *
     * @param Request $request
     * @return Application|Redirector|RedirectResponse
     */
    public function destroy(Request $request): Application|Redirector|RedirectResponse
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
