<?php

namespace App\Rules;

use App\Models\User;
use Auth;
use Illuminate\Contracts\Validation\Rule;

class ValidateUsername implements Rule
{
    const MINIMUM_USERNAME_LENGTH = 3;
    const MAXIMUM_USERNAME_LENGTH = 50;

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

        // Check alpha numeric
        if (!ctype_alnum($value)) {
            $this->errorType = 'alpha-num';
            return false;
        }

        // Check if username taken
        $user = Auth::user();
        if (!empty($user)) {
            if (User::whereNotIn('id', [$user->id])
                ->where('username', $value)
                ->exists()
            ) {
                $this->errorType = 'exists';
                return false;
            }
        } else {
            if (User::where('username', $value)->exists()) {
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
        if ($this->errorType == 'length')
            return __('validation.between.string', ['min' => self::MINIMUM_USERNAME_LENGTH, 'max' => self::MAXIMUM_USERNAME_LENGTH]);
        else if ($this->errorType == 'alpha-num')
            return __('validation.alpha_num');
        else if ($this->errorType == 'exists')
            return __('validation.unique');

        return 'The :attribute is invalid.';
    }
}
