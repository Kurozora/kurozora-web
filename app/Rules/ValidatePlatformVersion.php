<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidatePlatformVersion implements Rule
{
    const int MAX_VERSION_LENGTH = 15;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return
            preg_match("#^(([0-9])+(\.{0,1}([0-9]))*)+$#", $value) &&
            strlen($value) <= self::MAX_VERSION_LENGTH;
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
