<?php

namespace App\Http\Livewire\Profile;

use App\Actions\Web\Auth\DisableTwoFactorAuthentication;
use App\Actions\Web\Auth\EnableTwoFactorAuthentication;
use App\Actions\Web\Auth\GenerateNewRecoveryCodes;
use App\Traits\Web\Auth\ConfirmsPasswords;
use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;

class TwoFactorAuthenticationForm extends Component
{
    use ConfirmsPasswords;

    /**
     * Indicates if two factor authentication QR code is being displayed.
     *
     * @var bool
     */
    public bool $showingQrCode = false;

    /**
     * Indicates if two factor authentication recovery codes are being displayed.
     *
     * @var bool
     */
    public bool $showingRecoveryCodes = false;

    /**
     * Enable two factor authentication for the user.
     *
     * @param EnableTwoFactorAuthentication $enable
     *
     * @return void
     * @throws AuthorizationException
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function enableTwoFactorAuthentication(EnableTwoFactorAuthentication $enable)
    {
        $this->ensurePasswordIsConfirmed();

        $enable(Auth::user());

        $this->showingQrCode = true;
        $this->showingRecoveryCodes = true;
    }

    /**
     * Display the user's recovery codes.
     *
     * @return void
     * @throws AuthorizationException
     */
    public function showRecoveryCodes()
    {
        $this->ensurePasswordIsConfirmed();

        $this->showingRecoveryCodes = true;
    }

    /**
     * Generate new recovery codes for the user.
     *
     * @param GenerateNewRecoveryCodes $generate
     *
     * @return void
     * @throws AuthorizationException
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate)
    {
        $this->ensurePasswordIsConfirmed();

        $generate(Auth::user());

        $this->showingRecoveryCodes = true;
    }

    /**
     * Disable two factor authentication for the user.
     *
     * @param DisableTwoFactorAuthentication $disable
     *
     * @return void
     * @throws AuthorizationException
     */
    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable)
    {
        $this->ensurePasswordIsConfirmed();

        $disable(Auth::user());
    }

    /**
     * Determine if two factor authentication is enabled.
     *
     * @return bool
     */
    public function getEnabledProperty(): bool
    {
        return !empty(Auth::user()->two_factor_secret);
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
