<?php

namespace App\Rules;

use App\Models\Anime;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateAnimeIDIsTracked implements ValidationRule
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
        // Find the anime instance
        $anime = Anime::with('translations')
            ->firstWhere('id', $value);

        if (!auth()->user()->hasTracked($anime)) {
            $fail($this->message($anime->title));
        }
    }

    /**
     * Get the validation error message.
     *
     * @param string $title
     *
     * @return string
     */
    public function message(string $title): string
    {
        return __('Please add :x to your library first.', ['x' => $title]);
    }
}
