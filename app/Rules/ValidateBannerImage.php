<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateBannerImage implements ValidationRule
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
        $user = auth()->user();
        $isProOrSubscribed = $user?->is_pro || $user?->is_subscribed;
        $allowedMimes = $isProOrSubscribed ? 'mimes:webp,jpg,png,gif' : 'mimes:webp,jpg,png';
        $rules = ['nullable', 'image', 'max:2048', $allowedMimes];

        $value = $value == 'null' ? null : $value;
        $imgValidator = Validator::make([$attribute => $value], [
            $attribute => $rules,
        ]);

        if ($imgValidator->fails()) {
            $fail($imgValidator->errors()->first());
        }
    }
}
