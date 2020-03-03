<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateVendorName implements Rule
{
    const VALID_VENDORS = [
        'Apple'
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
        return in_array($value, self::VALID_VENDORS, true);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Valid device vendor names are ' . implode(', ', self::VALID_VENDORS) . '.';
    }
}
