<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidatePassword implements Rule
{
    /**
     * The minimum length of the password.
     *
     * @var int $minLength
     */
    protected int $minLength = 5;

    /**
     * The maximum length of the password.
     *
     * @var int $maxLength
     */
    protected int $maxLength = 255;

    /**
     * Indicates if the password must contain one uppercase character.
     *
     * @var bool $requireUppercase
     */
    protected bool $requireUppercase = true;

    /**
     * Indicates if the password must contain one numeric digit.
     *
     * @var bool $requireNumeric
     */
    protected bool $requireNumeric = true;

    /**
     * Indicates if the password must contain one special character.
     *
     * @var bool $requireSpecialCharacter
     */
    protected bool $requireSpecialCharacter = true;

    /**
     * The message that should be used when validation fails.
     *
     * @var string $message
     */
    protected string $message = '';

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $stringLength = str($value)->length();

        if ($this->requireUppercase && str($value)->lower() === $value) {
            return false;
        }

        if ($this->requireNumeric && !preg_match('/[0-9]/', $value)) {
            return false;
        }

        if ($this->requireSpecialCharacter && !preg_match('/[\W_]/', $value)) {
            return false;
        }

        return $stringLength >= $this->minLength && $stringLength <= $this->maxLength;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        if ($this->message) {
            return $this->message;
        }

        return match (true) {
            $this->requireUppercase
            && !$this->requireNumeric
            && !$this->requireSpecialCharacter => __('The :attribute must be at least :minLength and at most :maxLength characters and contain at least one uppercase character.', [
                'minLength' => $this->minLength,
                'maxLength' => $this->maxLength
            ]),
            $this->requireNumeric
            && !$this->requireUppercase
            && !$this->requireSpecialCharacter => __('The :attribute must be at least :minLength and at most :maxLength characters and contain at least one number.', [
                'minLength' => $this->minLength,
                'maxLength' => $this->maxLength
            ]),
            $this->requireSpecialCharacter
            && !$this->requireUppercase
            && !$this->requireNumeric => __('The :attribute must be at least :minLength and at most :maxLength characters and contain at least one special character.', [
                'minLength' => $this->minLength,
                'maxLength' => $this->maxLength
            ]),
            $this->requireUppercase
            && $this->requireNumeric
            && !$this->requireSpecialCharacter => __('The :attribute must be at least :minLength and at most :maxLength characters and contain at least one uppercase character and one number.', [
                'minLength' => $this->minLength,
                'maxLength' => $this->maxLength
            ]),
            $this->requireUppercase
            && $this->requireSpecialCharacter
            && !$this->requireNumeric => __('The :attribute must be at least :minLength and at most :maxLength characters and contain at least one uppercase character and one special character.', [
                'minLength' => $this->minLength,
                'maxLength' => $this->maxLength
            ]),
            $this->requireUppercase
            && $this->requireNumeric
            && $this->requireSpecialCharacter => __('The :attribute must be at least :minLength and at most :maxLength characters and contain at least one uppercase character, one number, and one special character.', [
                'minLength' => $this->minLength,
                'maxLength' => $this->maxLength
            ]),
            default => __('The :attribute must be at least :minLength and at most :maxLength characters.', [
                'minLength' => $this->minLength,
                'maxLength' => $this->maxLength
            ]),
        };
    }

    /**
     * Set the minimum length of the password.
     *
     * @param  int  $minLength
     * @return $this
     */
    public function minLength(int $minLength): ValidatePassword
    {
        $this->minLength = $minLength;
        return $this;
    }

    /**
     * Set the maximum length of the password.
     *
     * @param  int  $maxLength
     * @return $this
     */
    public function maxLength(int $maxLength): ValidatePassword
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * Indicate whether at least one uppercase character is required.
     *
     * @param bool $requireUppercase
     * @return $this
     */
    public function requireUppercase(bool $requireUppercase): ValidatePassword
    {
        $this->requireUppercase = $requireUppercase;
        return $this;
    }

    /**
     * Indicate whether at least one numeric digit is required.
     *
     * @param bool $requireNumeric
     * @return $this
     */
    public function requireNumeric(bool $requireNumeric): ValidatePassword
    {
        $this->requireNumeric = $requireNumeric;
        return $this;
    }

    /**
     * Indicate whether at least one special character is required.
     *
     * @param bool $requireSpecialCharacter
     * @return $this
     */
    public function requireSpecialCharacter(bool $requireSpecialCharacter): ValidatePassword
    {
        $this->requireSpecialCharacter = $requireSpecialCharacter;
        return $this;
    }

    /**
     * Set the message that should be used when the rule fails.
     *
     * @param  string  $message
     * @return $this
     */
    public function withMessage(string $message): ValidatePassword
    {
        $this->message = $message;
        return $this;
    }
}
