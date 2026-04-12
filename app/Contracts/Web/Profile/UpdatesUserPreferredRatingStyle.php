<?php

namespace App\Contracts\Web\Profile;

use App\Models\User;

interface UpdatesUserPreferredRatingStyle
{
    /**
     * Validate and update the given user's preferred rating style.
     *
     * @param User  $user
     * @param array  $input
     * @return void
     */
    public function update(User $user, array $input): void;
}
