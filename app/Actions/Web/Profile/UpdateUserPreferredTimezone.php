<?php

namespace App\Actions\Web\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredTimezone;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateUserPreferredTimezone implements UpdatesUserPreferredTimezone
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param User $user
     * @param array $input
     *
     * @return void
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'timezone' => ['required', 'timezone:all'],
        ])->validateWithBag('updatePreferredTimezone');

        $user->update([
            'timezone' => (string) $input['timezone']
        ]);
    }
}
