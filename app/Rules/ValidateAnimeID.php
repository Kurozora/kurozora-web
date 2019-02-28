<?php

namespace App\Rules;

use App\Anime;
use Illuminate\Contracts\Validation\Rule;

class ValidateAnimeID implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Reject if it's not a number
        if(!is_numeric($value)) return false;

        // Check whether the Anime exists
        return Anime::where('id', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The given Anime was not found.';
    }
}
