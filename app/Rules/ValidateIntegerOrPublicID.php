<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateIntegerOrPublicID implements ValidationRule
{
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
        $isInteger = filter_var($value, FILTER_VALIDATE_INT) !== false;
        $isPublicID = preg_match('/^[A-Za-z0-9_-]{16}$/', $value);

        if (!$isInteger && !$isPublicID) {
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
        return __('The :attribute must be an integer or a valid ID.', ['attribute' => $attribute]);
    }
}
