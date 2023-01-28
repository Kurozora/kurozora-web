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
        if (is_numeric($value)) {
            return UserLibraryStatus::hasValue((int) $value);
        } else if (is_string($value)) {
            return UserLibraryStatus::hasKey($value);
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('Pick a valid library status: :x', ['x' => implode(', ', UserLibraryStatus::getValues())]);
    }
}
