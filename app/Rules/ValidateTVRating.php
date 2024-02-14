<?php

namespace App\Rules;

use App\Models\TvRating;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateTVRating implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rules = ['integer', 'not_in:0,1'];

        if ($value != -1) {
            $rules[] = 'exists:' . TvRating::TABLE_NAME . ',id';
        }

        $tvRatingValidator = Validator::make([$attribute => $value], [
            $attribute => $rules,
        ]);

        if ($tvRatingValidator->fails()) {
            $fail($tvRatingValidator->errors()->first());
        }
    }
}
