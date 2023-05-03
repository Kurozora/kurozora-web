<?php

namespace App\Actions\Web;

use App\Contracts\UpdatesUserAccountInformation;
use App\Models\User;
use App\Rules\ValidateEmail;
use App\Rules\ValidateUserSlug;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateUserAccountInformation implements UpdatesUserAccountInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param User $user
     * @param array $input
     *
     * @return void
     */
    public function update(User $user, array $input): void
    {
        $rules = [
            'email' => ['required', new ValidateEmail, Rule::unique(User::TABLE_NAME)->ignore($user->id)],
        ];

        if ($user->is_subscribed || $user->can_change_username) {
            $rules = array_merge($rules, [
                'username' => ['required', new ValidateUserSlug],
            ]);
        }

        Validator::make($input, $rules)->validateWithBag('updateAccountInformation');

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'email' => $input['email'],
            ])->save();
        }

        if ($user->is_subscribed || $user->can_change_username) {
            if ($input['username'] !== $user->slug) {
                $user->forceFill([
                    'slug' => $input['username']
                ]);
                $user->save();
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
    protected function updateVerifiedUser(User|MustVerifyEmail $user, array $input): void
    {
        $user->forceFill([
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
