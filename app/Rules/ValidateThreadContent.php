<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateThreadContent implements Rule
{
    const MINIMUM_THREAD_CONTENT_LENGTH = 2;

    protected $errorType;

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
        if (!is_string($value) || !strlen($value)) {
            $this->errorType = 'short';
            return false;
        }

        // Check minimum length
        if (strlen($value) < self::MINIMUM_THREAD_CONTENT_LENGTH) {
            $this->errorType = 'short';
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
        if ($this->errorType == 'short') {
            return __('validation.lt.string', ['value' => self::MINIMUM_THREAD_CONTENT_LENGTH]);
        }

        return 'The :attribute is invalid.';
    }
}
