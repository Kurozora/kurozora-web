<?php

namespace App\Traits\Web\Auth;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

trait ConfirmsPasswords
{
    /**
     * Indicates if the user's password is being confirmed.
     *
     * @var bool
     */
    public bool $confirmingPassword = false;

    /**
     * The ID of the operation being confirmed.
     *
     * @var string|null
     */
    public ?string $confirmableId = null;

    /**
     * The user's password.
     *
     * @var string
     */
    public string $confirmablePassword = '';

    /**
     * Start confirming the user's password.
     *
     * @param  string  $confirmableId
     * @return void
     */
    public function startConfirmingPassword(string $confirmableId)
    {
        $this->resetErrorBag();

        if ($this->passwordIsConfirmed()) {
            return $this->dispatchBrowserEvent('password-confirmed', [
                'id' => $confirmableId,
            ]);
        }

        $this->confirmingPassword = true;
        $this->confirmableId = $confirmableId;
        $this->confirmablePassword = '';

        $this->dispatchBrowserEvent('confirming-password');
    }

    /**
     * Stop confirming the user's password.
     *
     * @return void
     */
    public function stopConfirmingPassword()
    {
        $this->confirmingPassword = false;
        $this->confirmableId = null;
        $this->confirmablePassword = '';
    }

    /**
     * Confirm the user's password.
     *
     * @return void
     * @throws ValidationException
     */
    public function confirmPassword()
    {
        $authenticated = Auth::validate([
            'email' => Auth::user()->email,
            'password' => $this->confirmablePassword,
        ]);

        if (!$authenticated) {
            throw ValidationException::withMessages([
                'confirmable_password' => [__('This password does not match our records.')],
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->dispatchBrowserEvent('password-confirmed', [
            'id' => $this->confirmableId,
        ]);

        $this->stopConfirmingPassword();
    }

    /**
     * Ensure that the user's password has been recently confirmed.
     *
     * @param ?int $maximumSecondsSinceConfirmation
     *
     * @return void
     * @throws AuthorizationException
     */
    protected function ensurePasswordIsConfirmed(?int $maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        return $this->passwordIsConfirmed($maximumSecondsSinceConfirmation)
            ? null
            : throw new AuthorizationException('There was an error while authenticating.');
    }

    /**
     * Determine if the user's password has been recently confirmed.
     *
     * @param ?int $maximumSecondsSinceConfirmation
     * @return bool
     */
    protected function passwordIsConfirmed(?int $maximumSecondsSinceConfirmation = null): bool
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        return (time() - session('auth.password_confirmed_at', 0)) < $maximumSecondsSinceConfirmation;
    }
}
