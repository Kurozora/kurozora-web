<?php

namespace App\Contracts\Web\Profile;

use App\Models\User;

interface UpdatesUserPreferredTvRating
{
    /**
     * Validate and update the given user's preferred TV rating.
     *
     * @param User  $user
     * @param array  $input
     * @return void
     */
    public function update(User $user, array $input);
}
