<?php

namespace App\Actions\Web;

use App\Contracts\UpdatesUserProfileInformation;
use App\Enums\MediaCollection;
use App\Models\User;
use App\Rules\ValidateBannerImage;
use App\Rules\ValidateNickname;
use App\Rules\ValidateProfileImage;
use App\Rules\ValidateUserBiography;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
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
    public function update(User $user, array $input): void
    {
        $updatedAttributes = [];
        $rules = [
            'nickname' => ['required', new ValidateNickname],
            'biography' => ['bail', new ValidateUserBiography],
            'profileImage' => ['bail', new ValidateProfileImage],
            'bannerImage' => ['bail', new ValidateBannerImage],
        ];

        Validator::make($input, $rules)->validateWithBag('updateProfileInformation');

        if (isset($input['profileImage'])) {
            $user->updateImageMedia(MediaCollection::Profile(), $input['profileImage']->getRealPath());
        }

        if (isset($input['bannerImage'])) {
            $user->updateImageMedia(MediaCollection::Banner(), $input['bannerImage']->getRealPath());
        }

        if (isset($input['biography'])) {
            $updatedAttributes['biography'] = $input['biography'];
        }

        if ($input['nickname'] !== $user->username) {
            $updatedAttributes['username'] = $input['nickname'];
        }

        $user->update($updatedAttributes);
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param User|MustVerifyEmail $user
     * @param array $input
     * @return void
     */
    protected function updateVerifiedUser(User|MustVerifyEmail $user, array $input): void
    {
        $user->forceFill([
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
