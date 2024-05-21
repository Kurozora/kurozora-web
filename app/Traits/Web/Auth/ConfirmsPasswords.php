<?php

namespace App\Traits\Web\Auth;

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
     * @param string $confirmableId
     * @return void
     */
    public function startConfirmingPassword(string $confirmableId): void
    {
        $this->resetErrorBag();

        if ($this->passwordIsConfirmed()) {
            $this->dispatch('password-confirmed', id: $confirmableId);
            return;
        }

        $this->confirmingPassword = true;
        $this->confirmableId = $confirmableId;
        $this->confirmablePassword = '';

        $this->dispatch('confirming-password');
    }

    /**
     * Stop confirming the user's password.
     *
     * @return void
     */
    public function stopConfirmingPassword(): void
    {
        $this->confirmingPassword = false;
        $this->confirmableId = null;
        $this->confirmablePassword = '';
    }

    /**
     * Confirm the user's password.
     *
     * @return void
     */
    public function confirmPassword(): void
    {
        if (!auth()->validate([
            'username' => auth()->user()->username,
            'password' => $this->confirmablePassword
        ])) {
            throw ValidationException::withMessages([
                'confirmable_password' => [__('This password does not match our records.')],
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->dispatch('password-confirmed', id: $this->confirmableId);

        $this->stopConfirmingPassword();
    }

    /**
     * Ensure that the user's password has been recently confirmed.
     *
     * @param int|null $maximumSecondsSinceConfirmation
     * @return void
     */
    protected function ensurePasswordIsConfirmed(?int $maximumSecondsSinceConfirmation = null): void
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        if (!$this->passwordIsConfirmed($maximumSecondsSinceConfirmation)) {
            abort(403);
        }
    }

    /**
     * Determine if the user's password has been recently confirmed.
     *
     * @param int|null $maximumSecondsSinceConfirmation
     * @return bool
     */
    protected function passwordIsConfirmed(?int $maximumSecondsSinceConfirmation = null): bool
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        return (time() - session('auth.password_confirmed_at', 0)) < $maximumSecondsSinceConfirmation;
    }
}
