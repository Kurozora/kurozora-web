<?php

namespace App\Rules;

use App\User;
use Illuminate\Contracts\Validation\Rule;

class ValidateEmail implements Rule
{
    const MINIMUM_EMAIL_LENGTH = 5;
    const MAXIMUM_EMAIL_LENGTH = 255;

    protected $errorType;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Empty string does not pass
        if(!is_string($value) || !strlen($value)) {
            $this->errorType = 'length';
            return false;
        }

        // Check minimum length
        if(strlen($value) < self::MINIMUM_EMAIL_LENGTH) {
            $this->errorType = 'length';
            return false;
        }

        // Check maximum length
        if(strlen($value) > self::MAXIMUM_EMAIL_LENGTH) {
            $this->errorType = 'length';
            return false;
        }

        // Check if valid email format
        if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errorType = 'not-email';
            return false;
        }

        // Check if username taken
        if(User::where('email', $value)->exists()) {
            $this->errorType = 'exists';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if($this->errorType == 'length')
            return trans('validation.between.string', ['min' => self::MINIMUM_EMAIL_LENGTH, 'max' => self::MAXIMUM_EMAIL_LENGTH]);
        else if($this->errorType == 'not-email')
            return trans('validation.email');
        else if($this->errorType == 'exists')
            return trans('validation.unique');

        return 'The email is invalid.';
    }
}
