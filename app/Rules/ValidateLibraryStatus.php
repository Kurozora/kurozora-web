<?php

namespace App\Rules;

use App\Enums\UserLibraryStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateLibraryStatus implements ValidationRule
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
        if (is_numeric($value)) {
            if (!UserLibraryStatus::hasValue((int) $value)) {
                $fail($this->message());
            }

            return;
        } else if (is_string($value)) {
            if (!UserLibraryStatus::hasKey($value)) {
                $fail($this->message());
            }

            return;
        }

        $fail($this->message());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('Pick a valid library status: :x', ['x' => implode(', ', UserLibraryStatus::getValues())]);
    }
}
