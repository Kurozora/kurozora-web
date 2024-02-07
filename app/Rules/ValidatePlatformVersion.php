<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidatePlatformVersion implements ValidationRule
{
    const int MAX_VERSION_LENGTH = 15;

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
        if (!(preg_match('#^(([0-9])+(\.?([0-9]))*)+$#', $value) &&
            strlen($value) <= self::MAX_VERSION_LENGTH)) {
            $fail($this->message($attribute));
        }
    }

    /**
     * Get the validation error message.
     *
     * @param string $attribute
     *
     * @return string
     */
    public function message(string $attribute): string
    {
        return __('The :attribute is not in a valid format.', ['attribute' => $attribute]);
    }
}
