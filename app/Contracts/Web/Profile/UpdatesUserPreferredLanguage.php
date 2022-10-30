<?php

namespace App\Contracts\Web\Profile;

use App\Models\User;

interface UpdatesUserPreferredLanguage
{
    /**
     * Validate and update the given user's preferred language.
     *
     * @param User  $user
     * @param array  $input
     * @return void
     */
    public function update(User $user, array $input): void;
}
