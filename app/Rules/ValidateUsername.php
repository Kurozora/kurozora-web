<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ValidateUsername implements Rule
{
    const MINIMUM_USERNAME_LENGTH = 3;
    const MAXIMUM_USERNAME_LENGTH = 30;

    /** @var string $errorType */
    protected string $errorType;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // Empty string does not pass
        if (!is_string($value) || !strlen($value)) {
            $this->errorType = 'length';
            return false;
        }

        // Check minimum length
        if (strlen($value) < self::MINIMUM_USERNAME_LENGTH) {
            $this->errorType = 'length';
            return false;
        }

        // Check maximum length
        if (strlen($value) > self::MAXIMUM_USERNAME_LENGTH) {
            $this->errorType = 'length';
            return false;
        }

        // Check alphanumeric and space
        if (!ctype_alnum(str_replace(['-', '_'], '', $value))) {
            $this->errorType = 'alpha-num';
            return false;
        }

        // Check if username taken
        $user = auth()->user();

        if (!empty($user)) {
            if (User::whereNotIn('id', [$user->id])
                ->where('slug', $value)
                ->exists()
            ) {
                $this->errorType = 'exists';
                return false;
            }
        } else {
            if (User::where('slug', $value)->exists()) {
                $this->errorType = 'exists';
                return false;
            }
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
        return match ($this->errorType) {
            'length' => __('validation.between.string', ['min' => self::MINIMUM_USERNAME_LENGTH, 'max' => self::MAXIMUM_USERNAME_LENGTH]),
            'alpha-num' => __('validation.alpha_dash'),
            'exists' => __('validation.unique'),
            default => __('The :attribute is invalid.'),
        };
    }
}
