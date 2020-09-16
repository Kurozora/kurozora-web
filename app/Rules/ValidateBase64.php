<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateBase64 implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // Empty string does not pass
        return base64_decode($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a base64 string.';
    }
}
