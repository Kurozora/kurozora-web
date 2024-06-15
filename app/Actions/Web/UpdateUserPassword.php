<?php

namespace App\Actions\Web;

use App\Contracts\UpdatesUserPasswords;
use App\Rules\ValidatePassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateUserPassword implements UpdatesUserPasswords
{
    /**
     * Validate and update the user's password.
     *
     * @param mixed $user
     * @param array $input
     *
     * @return void
     * @throws ValidationException
     */
    public function update($user, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required', 'string'],
            'password' => ['required', new ValidatePassword, 'confirmed'],
        ])->after(function ($validator) use ($user, $input) {
            if (!Hash::check($input['current_password'], $user->password)) {
                $validator->errors()->add('current_password', __('The provided password does not match your current password.'));
            }
        })->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
