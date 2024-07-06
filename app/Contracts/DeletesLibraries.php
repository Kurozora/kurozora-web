<?php

namespace App\Contracts;

use App\Models\User;

interface DeletesLibraries
{
    /**
     * Delete the given user's library.
     *
     * @param User  $user
     * @param array $input
     *
     * @return void
     */
    public function delete(User $user, array $input): void;
}
