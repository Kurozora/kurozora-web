<?php

namespace App\Livewire\Profile;

use App\Actions\Web\Auth\ConfirmTwoFactorAuthentication;
use App\Actions\Web\Auth\DisableTwoFactorAuthentication;
use App\Actions\Web\Auth\EnableTwoFactorAuthentication;
use App\Actions\Web\Auth\GenerateNewRecoveryCodes;
use App\Models\User;
use App\Traits\Web\Auth\ConfirmsPasswords;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TwoFactorAuthenticationForm extends Component
{
    use ConfirmsPasswords;

    /**
     * Indicates if two-factor authentication QR code is being displayed.
     *
     * @var bool
     */
    public bool $showingQrCode = false;

    /**
     * Indicates if the two-factor authentication confirmation input and button are being displayed.
     *
     * @var bool
     */
    public bool $showingConfirmation = false;

    /**
     * Indicates if two-factor authentication recovery codes are being displayed.
     *
     * @var bool
     */
    public bool $showingRecoveryCodes = false;

    /**
     * The OTP code for confirming two-factor authentication.
     *
     * @var string
     */
    public string $code = '';

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount(): void
    {
        if (is_null(auth()->user()->two_factor_confirmed_at)) {
            app(DisableTwoFactorAuthentication::class)(auth()->user());
        }
    }

    /**
     * Enable two-factor authentication for the user.
     *
     * @param EnableTwoFactorAuthentication $enable
     * @return void
     */
    public function enableTwoFactorAuthentication(EnableTwoFactorAuthentication $enable): void
    {
        $this->ensurePasswordIsConfirmed();

        $enable(auth()->user());

        $this->showingQrCode = true;
        $this->showingConfirmation = true;
    }

    /**
     * Confirm two-factor authentication for the user.
     *
     * @param ConfirmTwoFactorAuthentication $confirm
     * @return void
     */
    public function confirmTwoFactorAuthentication(ConfirmTwoFactorAuthentication $confirm): void
    {
        $this->ensurePasswordIsConfirmed();

        $confirm(auth()->user(), $this->code);

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = true;
    }

    /**
     * Display the user's recovery codes.
     *
     * @return void
     */
    public function showRecoveryCodes(): void
    {
        $this->ensurePasswordIsConfirmed();

        $this->showingRecoveryCodes = true;
    }

    /**
     * Generate new recovery codes for the user.
     *
     * @param GenerateNewRecoveryCodes $generate
     * @return void
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate): void
    {
        $this->ensurePasswordIsConfirmed();

        $generate(auth()->user());

        $this->showingRecoveryCodes = true;
    }

    /**
     * Disable two-factor authentication for the user.
     *
     * @param DisableTwoFactorAuthentication $disable
     * @return void
     */
    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable): void
    {
        $this->ensurePasswordIsConfirmed();

        $disable(auth()->user());

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;
    }

    /**
     * Get the current user of the application.
     *
     * @return User|null
     */
    public function getUserProperty(): User|null
    {
        return auth()->user();
    }

    /**
     * Determine if two-factor authentication is enabled.
     *
     * @return bool
     */
    public function getEnabledProperty(): bool
    {
        return !empty($this->user->two_factor_secret);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.two-factor-authentication-form');
    }
}
