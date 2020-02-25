<?php

namespace App\Rules;

use App\User;
use Illuminate\Contracts\Validation\Rule;

class ValidateEmail implements Rule
{
    const MINIMUM_EMAIL_LENGTH = 5;
    const MAXIMUM_EMAIL_LENGTH = 255;

    /**
     * Options for email validation.
     *
     * Available options:
     * - `must-be-taken`: if set to true, the email must be in use.
     * - `must-be-available`: if set to true, the email must be in available for use.
     *
     * @var array $options
     */
    protected $options;

    /** @var string $error */
    protected $error = 'The :attribute is invalid';

    /**
     * @param array $options
     */
    function __construct($options = [])
    {
        $this->options = $options;
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
        if(!is_string($value) || !strlen($value))
            return $this->fail(trans('validation.min', ['min' => self::MINIMUM_EMAIL_LENGTH]));

        // Check minimum length
        if(strlen($value) < self::MINIMUM_EMAIL_LENGTH)
            return $this->fail(trans('validation.min', ['min' => self::MINIMUM_EMAIL_LENGTH]));

        // Check maximum length
        if(strlen($value) > self::MAXIMUM_EMAIL_LENGTH)
            return $this->fail(trans('validation.max', ['max' => self::MINIMUM_EMAIL_LENGTH]));

        // Check if valid email format
        if(!filter_var($value, FILTER_VALIDATE_EMAIL))
            return $this->fail(trans('validation.email'));

        // (option) The email must be taken
        if($this->option('must-be-taken', false) === true) {
            if (!User::where('email', $value)->exists())
                return $this->fail(trans('validation.exists'));
        }

        // (option) The email must be available
        if($this->option('must-be-available', false) === true) {
            if (User::where('email', $value)->exists())
                return $this->fail(trans('validation.unique'));
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
        return $this->error;
    }

    /**
     * Fails the validator and sets the error.
     *
     * @param string $error
     * @return bool
     */
    private function fail($error)
    {
        $this->error = $error;
        return false;
    }

    /**
     * Gets the value of an option or returns the default ..
     * .. value if the option is not set.
     *
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    private function option($option, $default)
    {
        if(isset($this->options[$option]))
            return $this->options[$option];
        else return $default;
    }
}
