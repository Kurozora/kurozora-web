<?php

namespace Laravel\Nova\Exceptions;

use Illuminate\Auth\AuthenticationException as BaseAuthenticationException;
use Illuminate\Support\Facades\Route;

class AuthenticationException extends BaseAuthenticationException
{
    /**
     * Render the exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 401);
        } elseif ($request->is('nova-api/*') || $request->is('nova-vendor/*')) {
            return redirect()->to($this->location());
        }

        return redirect()->guest($this->location());
    }

    /**
     * Determine the location the user should be redirected to.
     *
     * @return string
     */
    protected function location()
    {
        if (Route::getRoutes()->hasNamedRoute('nova.login')) {
            return route('nova.login');
        } elseif (Route::getRoutes()->hasNamedRoute('login')) {
            return route('login');
        }

        return '/login';
    }
}
