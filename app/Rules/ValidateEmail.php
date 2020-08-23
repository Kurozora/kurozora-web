<?php

namespace App\Rules;

use App\Helpers\OptionsBag;
use App\User;
use Illuminate\Contracts\Validation\Rule;
use InvalidArgumentException;

class ValidateEmail implements Rule
{
    const MINIMUM_EMAIL_LENGTH = 5;
    const MAXIMUM_EMAIL_LENGTH = 255;

    /** @var OptionsBag $options */
    protected $options;

    /** @var string $error */
    protected $error = 'The :attribute is invalid';

    /**
     * Available options:
     * - `must-be-taken`: if set to true, the email must be in use.
     * - `must-be-available`: if set to true, the email must be in available for use.
     *
     * @param array $options
     */
    function __construct($options = [])
    {
        // The `must-be-taken` and `must-be-available` options cannot be used simultaneously
        if(isset($options['must-be-taken']) && isset($options['must-be-available']))
            throw new InvalidArgumentException('The `must-be-taken` and `must-be-available` options cannot be used simultaneously.');

        // Set the options
        $this->options = new OptionsBag($options);
    }

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
        if($this->options->get('must-be-taken', false) === true) {
            if (!User::where('email', $value)->exists())
                return $this->fail(trans('validation.exists'));
        }

        // (option) The email must be available
        if($this->options->get('must-be-available', false) === true) {
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
    public function message(): string
    {
        return $this->error;
    }

    /**
     * Fails the validator and sets the error.
     *
     * @param string $error
     * @return bool
     */
    private function fail($error): bool
    {
        $this->error = $error;
        return false;
    }
}
