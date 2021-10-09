<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ValidateProfileImage implements Rule
{
    /** @var string $error */
    protected string $error;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
//        $user = Auth::user();
//        $userReceipt = $user->receipt;

        $rules = ['max:2048', 'nullable'];
        $allowedMimes = 'mimes:jpeg,jpg,png';
//        $allowedMimes = $userReceipt === null || !$userReceipt->is_subscribed ? 'mimes:jpeg,jpg,png' : 'mimes:jpeg,jpg,png,gif';
        array_push($rules, $allowedMimes);

        $imgValidator = Validator::make([$attribute => $value], [
            $attribute => $rules,
        ]);

        if ($imgValidator->fails()) {
            $this->error = $imgValidator->errors()->first();
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->error;
    }
}
