<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidatePlatformName implements ValidationRule
{
    const array VALID_PLATFORMS = [
        'iOS', 'Android', 'Web', 'Console', 'macOS', 'iPadOS', 'tvOS', 'watchOS', 'visionOS', 'Windows', 'Linux'
    ];

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
        if (!in_array($value, self::VALID_PLATFORMS, true)) {
            $fail($this->message());
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('Valid platform names are :x.', ['x' => implode(', ', self::VALID_PLATFORMS)]);
    }
}
