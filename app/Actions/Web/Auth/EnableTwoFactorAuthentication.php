<?php

namespace App\Actions\Web\Auth;

use App\Contracts\Web\Auth\TwoFactorAuthenticationProvider;
use App\Events\TwoFactorAuthenticationEnabled;
use App\Helpers\RecoveryCode;
use App\Models\User;
use Illuminate\Support\Collection;

class EnableTwoFactorAuthentication
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
     * Enable two-factor authentication for the user.
     *
     * @param User|null $user
     * @return void
     */
    public function __invoke(?User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();

        TwoFactorAuthenticationEnabled::dispatch($user);
    }
}
