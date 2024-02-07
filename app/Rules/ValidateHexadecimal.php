<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateHexadecimal implements ValidationRule
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
        // Empty string does not pass
        if (!ctype_xdigit($value)) {
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
        return __('The :attribute must be a hexadecimal string.', ['attribute' => $attribute]);
    }
}
