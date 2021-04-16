<?php

namespace App\Actions\Web;

use App\Models\User;
use App\Rules\ValidateProfileImage;
use App\Rules\ValidateEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use App\Contracts\UpdatesUserProfileInformation;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
     * @throws ValidationException
     */
    public function update(User $user, array $input)
    {
        Validator::make($input, [
            'email' => ['required', new ValidateEmail, Rule::unique(User::TABLE_NAME)->ignore($user->id)],
            'photo' => [new ValidateProfileImage],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfileImage($input['photo']->getRealPath());
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'email' => $input['email']
            ])->save();
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
