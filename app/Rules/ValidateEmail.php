<?php

namespace App\Rules;

use App\Helpers\OptionsBag;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use InvalidArgumentException;
use Validator;

class ValidateEmail implements Rule
{
    const int MINIMUM_EMAIL_LENGTH = 5;
    const int MAXIMUM_EMAIL_LENGTH = 255;

    /** @var OptionsBag $options */
    protected OptionsBag $options;

    /** @var string $error */
    protected string $error = 'The :attribute is invalid';

    /**
     * Available options:
     * - `must-be-taken`: if set to true, the email must be in use.
     * - `must-be-available`: if set to true, the email must be in available for use.
     *
     * @param array $options
     */
    function __construct(array $options = [])
    {
        // The `must-be-taken` and `must-be-available` options cannot be used simultaneously
        if (isset($options['must-be-taken']) && isset($options['must-be-available'])) {
            throw new InvalidArgumentException('The `must-be-taken` and `must-be-available` options cannot be used simultaneously.');
        }

        // Set the options
        $this->options = new OptionsBag($options);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $availabilityRule = '';
        $validationRules = ['required', 'min:'.self::MINIMUM_EMAIL_LENGTH, 'max:'.self::MAXIMUM_EMAIL_LENGTH, 'email:filter'];

        if ($this->options->get('must-be-taken', false) === true) {
            $availabilityRule = 'exists:' . User::TABLE_NAME . ',email';
        }

        if ($this->options->get('must-be-available', false) === true) {
            $availabilityRule = 'unique:' . User::TABLE_NAME . ',email';
        }

        if (!empty($availabilityRule)) {
            $validationRules[] = $availabilityRule;
        }

        $validator = Validator::make([$attribute => $value], [
            $attribute => $validationRules
        ]);

        $this->error = $validator->errors()->first();
        return $validator->passes();
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
}
