<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\VerifyEmailRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

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

        session()->flash('success', __('Your email has been verified!'));

        return redirect()->intended('/?verified=1');
    }
}
