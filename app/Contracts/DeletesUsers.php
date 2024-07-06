<?php

namespace App\Contracts;

use App\Models\User;

interface DeletesUsers
{
    /**
     * Delete the given user.
     *
     * @param User $user
     *
     * @return void
     */
    public function delete(User $user): void;
}
