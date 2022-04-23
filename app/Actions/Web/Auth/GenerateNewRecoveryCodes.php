<?php

namespace App\Actions\Web\Auth;

use App\Events\RecoveryCodesGenerated;
use App\Helpers\RecoveryCode;
use App\Models\User;
use Illuminate\Support\Collection;

class GenerateNewRecoveryCodes
{
    /**
     * Generate new recovery codes for the user.
     *
     * @param User|null $user
     * @return void
     */
    public function __invoke(?User $user): void
    {
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
