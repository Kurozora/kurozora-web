<?php

namespace App\Actions\Web;

use App\Contracts\UpdatesUserProfileInformation;
use App\Models\User;
use App\Rules\ValidateEmail;
use App\Rules\ValidateProfileImage;
use App\Rules\ValidateUsername;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param User $user
     * @param array $input
     *
     * @return void
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    public function update(User $user, array $input)
    {
        $rules = [
            'email' => ['required', new ValidateEmail, Rule::unique(User::TABLE_NAME)->ignore($user->id)],
            'photo' => [new ValidateProfileImage],
        ];

        if (settings('can_change_username')) {
            $rules = array_merge($rules, [
                'username' => ['required', new ValidateUsername]
            ]);
        }

        Validator::make($input, $rules)->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfileImage($input['photo']->getRealPath());
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'email' => $input['email'],
            ])->save();
        }

        if (settings('can_change_username')) {
            if ($input['username'] !== $user->username) {
                $user->forceFill([
                    'username' => $input['username']
                ])->save();

                settings('can_change_username', false, true);
            }
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param User|MustVerifyEmail $user
     * @param array $input
     * @return void
     */
    protected function updateVerifiedUser(User|MustVerifyEmail $user, array $input)
    {
        $user->forceFill([
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
