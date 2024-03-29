<?php

namespace App\Rules;

use App\Helpers\OptionsBag;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use InvalidArgumentException;
use Validator;

class ValidateEmail implements ValidationRule
{
    const int MINIMUM_EMAIL_LENGTH = 5;
    const int MAXIMUM_EMAIL_LENGTH = 255;

    /** @var OptionsBag $options */
    protected OptionsBag $options;

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
     * Run the validation rule.
     *
     * @param string                                       $attribute
     * @param mixed                                        $value
     * @param Closure(string): PotentiallyTranslatedString $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $availabilityRule = '';
        $validationRules = ['required', 'min:'.self::MINIMUM_EMAIL_LENGTH, 'max:'.self::MAXIMUM_EMAIL_LENGTH, 'email:filter,dns'];

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

        if ($validator->fails()) {
            $fail($validator->errors()->first());
        }
    }
}
