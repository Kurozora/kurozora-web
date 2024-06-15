<?php

namespace App\Actions\Web\Auth;

use App\Contracts\Web\Auth\TwoFactorAuthenticationProvider;
use App\Events\TwoFactorAuthenticationConfirmed;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorAuthentication
{
    /**
     * The two-factor authentication provider.
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
     * Confirm the two-factor authentication configuration for the user.
     *
     * @param User|null $user
     * @param string $code
     * @return void
     */
    public function __invoke(?User $user, string $code): void
    {
        if (empty($user->two_factor_secret) ||
            empty($code) ||
            !$this->provider->verify(decrypt($user->two_factor_secret), $code)) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two-factor authentication code was invalid.')],
            ])->errorBag('confirmTwoFactorAuthentication');
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        TwoFactorAuthenticationConfirmed::dispatch($user);
    }
}
