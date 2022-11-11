<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ValidateBannerImage implements Rule
{
    /** @var string $error */
    protected string $error;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $user = auth()->user();
        $isProOrSubscribed = $user?->is_pro || $user?->is_subscribed;

        $allowedMimes = $isProOrSubscribed ? 'mimes:webp,jpg,png,gif' : 'mimes:webp,jpg,png';
        $rules = ['nullable', 'image', 'max:2048', $allowedMimes];

        $value = $value == 'null' ? null : $value;
        $imgValidator = Validator::make([$attribute => $value], [
            $attribute => $rules,
        ]);

        if ($imgValidator->fails()) {
            $this->error = $imgValidator->errors()->first();
            return false;
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
        return $this->error;
    }
}
