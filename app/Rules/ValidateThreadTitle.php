<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateThreadTitle implements Rule
{
    const MINIMUM_THREAD_TITLE_LENGTH = 2;
    const MAXIMUM_THREAD_TITLE_LENGTH = 255;

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
            $this->errorType = 'length';
            return false;
        }

        // Check minimum length
        if (strlen($value) < self::MINIMUM_THREAD_TITLE_LENGTH) {
            $this->errorType = 'length';
            return false;
        }

        // Check maximum length
        if (strlen($value) > self::MAXIMUM_THREAD_TITLE_LENGTH) {
            $this->errorType = 'length';
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
        if ($this->errorType == 'length') {
            return __('validation.between.string', ['min' => self::MINIMUM_THREAD_TITLE_LENGTH, 'max' => self::MAXIMUM_THREAD_TITLE_LENGTH]);
        }

        return 'The :attribute is invalid.';
    }
}
