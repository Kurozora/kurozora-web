<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidatePlatformName implements Rule
{
    const VALID_PLATFORMS = [
        'iOS', 'Android', 'Web', 'Console', 'macOS', 'iPadOS', 'tvOS', 'watchOS'
    ];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array($value, self::VALID_PLATFORMS, true);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Valid platform names are ' . implode(', ', self::VALID_PLATFORMS) . '.';
    }
}
