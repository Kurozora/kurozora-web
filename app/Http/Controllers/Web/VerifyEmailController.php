<?php

namespace App\Http\Controllers\Web;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\VerifyEmailRequest;

class VerifyEmailController
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  VerifyEmailRequest  $request
     * @return RedirectResponse
     */
    public function __invoke(VerifyEmailRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended('/?verified=1');
    }
}
