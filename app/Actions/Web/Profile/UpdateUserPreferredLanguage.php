<?php

namespace App\Actions\Web\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredLanguage;
use App\Models\Language;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateUserPreferredLanguage implements UpdatesUserPreferredLanguage
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
            'language' => ['required', 'string', 'exists:' . Language::TABLE_NAME . ',code'],
        ])->validateWithBag('updatePreferredLanguage');

        $user->update([
            'language_id' => $input['language']
        ]);
    }
}
