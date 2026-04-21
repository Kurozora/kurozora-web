<?php

namespace App\Actions\Web\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredRatingStyle;
use App\Enums\RatingStyle;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateUserPreferredRatingStyle implements UpdatesUserPreferredRatingStyle
{
    /**
     * Validate and update the given user's preferred rating style.
     *
     * @param User $user
     * @param array $input
     *
     * @return void
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'rating_style' => [
                'required',
                'integer',
                Rule::in([
                    RatingStyle::QuickReaction,
                    RatingStyle::Standard,
                    RatingStyle::Advanced,
                    RatingStyle::Detailed,
                ]),
            ],
        ])->validateWithBag('updatePreferredRatingStyle');

        $user->update([
            'rating_style' => (int) $input['rating_style']
        ]);
    }
}
