<?php

namespace App\Actions\Web\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredTvRating;
use App\Models\TvRating;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateUserPreferredTvRating implements UpdatesUserPreferredTvRating
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param User $user
     * @param array $input
     *
     * @return void
     * @throws ValidationException
     */
    public function update(User $user, array $input)
    {
        $rules = ['required', 'integer', 'not_in:0,1'];

        if ($input['tv_rating'] != -1) {
            array_push($rules, 'exists:' . TvRating::TABLE_NAME . ',id');
        }

        Validator::make($input, [
            'tv_rating' => $rules,
        ])->validateWithBag('updatePreferredTvRating');

        settings('tv_rating', (integer) $input['tv_rating']);
    }
}
