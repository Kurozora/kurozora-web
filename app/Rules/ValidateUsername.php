<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Validator;

class ValidateUsername implements ValidationRule
{
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
        if (strlen($value) < User::MINIMUM_USERNAME_LENGTH) {
            $fail($this->message('length'));
            return;
        }

        // Check maximum length
        if (strlen($value) > User::MAXIMUM_USERNAME_LENGTH) {
            $fail($this->message('length'));
            return;
        }

        // Check alphanumeric and space
        if (!str_replace([' ', '-', '_'], '', $value)) {
            $fail($this->message('alpha-num'));
            return;
        }

        // Check if username is not an email
        if (
            Validator::make([], [])
                ->validateEmail('username', $value, [])
        ) {
            $fail($this->message('email'));
            return;
        }

        // Check if username is allowed
        if (collect(explode(',', config('username.banned_list')))->contains(strtolower($value))) {
            $fail($this->message('blocked-username'));
            return;
        }

        // Check if username is taken
        $user = auth()->user();
        if (!empty($user)) {
            if (User::whereNotIn('id', [$user->id])
                ->where('username', $value)
                ->exists()
            ) {
                $fail($this->message('exists'));
            }
        } else {
            if (User::where('username', $value)->exists()) {
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
            'length' => __('validation.between.string', ['min' => User::MINIMUM_USERNAME_LENGTH, 'max' => User::MAXIMUM_USERNAME_LENGTH]),
            'alpha-num' => __('validation.alpha_num'),
            'email' => __('The :attribute must not be an email address.'),
            'exists' => __('validation.unique'),
            'blocked-username' => __('This :attribute is not allowed. Please choose a different one.'),
            default => __('The :attribute is invalid.'),
        };
    }
}
