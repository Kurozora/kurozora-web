<?php

namespace App\Contracts\Web\Profile;

use App\Models\User;

interface ImportsUserAnimeLibrary
{
    /**
     * Validate and import the exported anime file to the user's Kurozora library.
     *
     * @param User  $user
     * @param array  $input
     * @return void
     */
    public function update(User $user, array $input);
}
