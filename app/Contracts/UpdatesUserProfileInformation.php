<?php

namespace App\Contracts;

use App\Models\User;

interface UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param User  $user
     * @param array $input
     *
     * @return void
     */
    public function update(User $user, array $input): void;
}
