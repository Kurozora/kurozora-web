<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateUserSlug implements ValidationRule
{
    const MINIMUM_USERNAME_LENGTH = 3;
    const MAXIMUM_USERNAME_LENGTH = 30;

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure(string): PotentiallyTranslatedString $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Empty string does not pass
        if (!is_string($value) || !strlen($value)) {
            $fail($this->message('length'));
            return;
        }

        // Check minimum length
        if (strlen($value) < self::MINIMUM_USERNAME_LENGTH) {
            $fail($this->message('length'));
            return;
        }

        // Check maximum length
        if (strlen($value) > self::MAXIMUM_USERNAME_LENGTH) {
            $fail($this->message('length'));
            return;
        }

        // Check alphanumeric and space
        if (!ctype_alnum(str_replace(['-', '_'], '', $value))) {
            $fail($this->message('alpha-num'));
            return;
        }

        // Check if username taken
        $user = auth()->user();

        if (!empty($user)) {
            if (User::whereNotIn('id', [$user->id])
                ->where('slug', $value)
                ->exists()
            ) {
                $fail($this->message('exists'));
            }
        } else {
            if (User::where('slug', $value)->exists()) {
                $fail($this->message('exists'));
            }
        }
    }

    /**
     * Get the validation error message.
     *
     * @param string $type
     * @return string
     */
    private function message(string $type): string
    {
        return match ($type) {
            'length' => __('validation.between.string', ['min' => self::MINIMUM_USERNAME_LENGTH, 'max' => self::MAXIMUM_USERNAME_LENGTH]),
            'alpha-num' => __('validation.alpha_dash'),
            'exists' => __('validation.unique'),
            default => __('The :attribute is invalid.'),
        };
    }
}
