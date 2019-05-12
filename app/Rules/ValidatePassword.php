<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidatePassword implements Rule
{
    const MINIMUM_PASSWORD_LENGTH = 5;
    const MAXIMUM_PASSWORD_LENGTH = 255;

    protected $errorType;

    /**
     * Whether or not it is required
     *
     * @var bool
     */
    protected $required;

    /**
     * ValidatePassword constructor.
     *
     * @param bool $required
     */
    function __construct($required = true) {
        $this->required = $required;
    }

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

            if($this->required) return false;
            else return true;
        }

        // Check minimum length
        if(strlen($value) < self::MINIMUM_PASSWORD_LENGTH) {
            $this->errorType = 'length';
            return false;
        }

        // Check maximum length
        if(strlen($value) > self::MAXIMUM_PASSWORD_LENGTH) {
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
    public function message()
    {
        if($this->errorType == 'length')
            return trans('validation.between.string', ['min' => self::MINIMUM_PASSWORD_LENGTH, 'max' => self::MAXIMUM_PASSWORD_LENGTH]);

        return 'The password is invalid.';
    }
}
