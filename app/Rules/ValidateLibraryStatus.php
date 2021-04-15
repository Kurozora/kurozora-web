<?php

namespace App\Rules;

use App\Enums\UserLibraryStatus;
use Illuminate\Contracts\Validation\Rule;

class ValidateLibraryStatus implements Rule
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
        if (!is_string($value) || !strlen($value)) return false;

        // Check if this status is valid
        return UserLibraryStatus::hasKey($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Pick a valid library status: ' .
            implode(', ', UserLibraryStatus::getKeys())
        ;
    }
}
