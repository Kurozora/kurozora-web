<?php

namespace App\Actions\Web\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredRatingStyle;
use App\Enums\RatingStyle;
use App\Models\User;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Support\Facades\Validator;
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
            'rating_style' => ['required', 'integer', new EnumValue(RatingStyle::class, false)],
        ])->validateWithBag('updatePreferredRatingStyle');

        $user->settings()
            ->firstOrCreate()
            ->update([
                'rating_style' => (int) $input['rating_style']
            ]);
    }
}
