<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateAPNDeviceToken implements Rule
{
    const int TOKEN_LENGTH = 64;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return is_string($value) && strlen($value) == self::TOKEN_LENGTH;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute is not in a valid format.';
    }
}
