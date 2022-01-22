<?php

namespace App\Contracts\Web\Profile;

use App\Models\User;

interface ImportsUserLibraryFromMAL
{
    /**
     * Validate and import the exported MAL file to the user's Kurozora library.
     *
     * @param User  $user
     * @param array  $input
     * @return void
     */
    public function update(User $user, array $input);
}
