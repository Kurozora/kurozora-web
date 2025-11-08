<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateModelIsTracked implements ValidationRule
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
        $models = $value;
        $user = auth()->user();
        $type = $models->first()->getMorphClass();
        $modelIDs = $models->pluck('id')->all();
        $trackedIDs = ($user->relationLoaded('library') ? $user->library : $user->library())
            ->where('trackable_type', '=', $type)
            ->whereIn('trackable_id', $modelIDs)
            ->pluck('trackable_id')
            ->all();

        // Determine which models are missing
        $missing = $models->whereNotIn('id', $trackedIDs);

        if ($missing->isNotEmpty()) {
            $titles = $missing->pluck('title');
            $fail($this->message($titles));
        }
    }

    /**
     * Get the validation error message.
     *
     * @param Collection $titles
     *
     * @return string
     */
    public function message(Collection $titles): string
    {
        $title = $titles->map(fn($title) => '"' . $title . '"')
            ->join(', ', __(' and '));
        return __('Please add :x to your library first.', ['x' => $title]);
    }
}
