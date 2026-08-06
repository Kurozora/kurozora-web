<?php

namespace App\Rules;

use App\Models\Minigames\Kotodama\Word;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateRevealableWord implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isRevealable = Word::where('id', $value)
            ->safeToReveal()
            ->exists();

        if (!$isRevealable) {
            $fail(__('That word is reserved for a daily puzzle.'));
        }
    }
}
