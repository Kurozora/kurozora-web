<?php

namespace App\Contracts;

use App\Models\User;

interface UpdatesUserPasswords
{
    /**
     * Validate and update the user's password.
     *
     * @param User  $user
     * @param array $input
     *
     * @return void
     */
    public function update(User $user, array $input): void;
}
