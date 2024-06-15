<?php

namespace App\Actions\Web\Auth;

use App\Events\TwoFactorAuthenticationDisabled;
use App\Models\User;

class DisableTwoFactorAuthentication
{
    /**
     * Disable two-factor authentication for the user.
     *
     * @param User|null $user
     *
     * @return void
     */
    public function __invoke(?User $user): void
    {
        if (!is_null($user->two_factor_secret) ||
            !is_null($user->two_factor_recovery_codes) ||
            !is_null($user->two_factor_confirmed_at)) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->save();

            TwoFactorAuthenticationDisabled::dispatch($user);
        }
    }
}
