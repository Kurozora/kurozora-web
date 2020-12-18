<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Called when a User is deleted
     *
     * @param User $user
     */
    public function deleted(User $user)
    {
        // ...
    }
}
