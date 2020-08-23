<?php

namespace App\Rules;

use App\Anime;
use App\User;
use Illuminate\Contracts\Validation\Rule;

class ValidateAnimeIDIsTracked implements Rule
{
    /** @var Anime $anime */
    protected Anime $anime;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // Find the anime instance
        $this->anime = Anime::firstWhere('id', $value);

        /** @var User $user */
        $user = auth()->user();

        return $user->isTracking($this->anime);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Please add ' . $this->anime->title . ' to your library first.';
    }
}
