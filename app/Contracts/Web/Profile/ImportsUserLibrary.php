<?php

namespace App\Contracts\Web\Profile;

use App\Models\User;

interface ImportsUserLibrary
{
    /**
     * Validate and import the exported library file to the user's Kurozora library.
     *
     * @param User  $user
     * @param array  $input
     * @return void
     */
    public function update(User $user, array $input): void;
}
