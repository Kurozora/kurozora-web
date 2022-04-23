<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession as BaseAuthenticateSession;

class AuthenticateSession extends BaseAuthenticateSession
{
    /**
     * Log the user out of the application.
     *
     * @param  Request  $request
     * @return void
     *
     * @throws AuthenticationException
     */
    protected function logout($request): void
    {
        $this->guard()->logoutCurrentDevice();

        $request->session()->flush();

        throw new AuthenticationException('Unauthenticated.', [$this->auth->getDefaultDriver()], route('sign-in'));
    }
}
