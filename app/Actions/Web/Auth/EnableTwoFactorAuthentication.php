<?php

namespace App\Actions\Web\Auth;

use App\Helpers\RecoveryCode;
use App\Providers\TwoFactorAuthenticationProvider;
use Illuminate\Support\Collection;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;

class EnableTwoFactorAuthentication
{
    /**
     * The two factor authentication provider.
     *
     * @var TwoFactorAuthenticationProvider
     */
    protected TwoFactorAuthenticationProvider $provider;

    /**
     * Create a new action instance.
     *
     * @param  TwoFactorAuthenticationProvider  $provider
     * @return void
     */
    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Enable two factor authentication for the user.
     *
     * @param mixed $user
     *
     * @return void
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function __invoke(mixed $user)
    {
        $user->forceFill([
            'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();
    }
}
