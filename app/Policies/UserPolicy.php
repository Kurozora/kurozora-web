<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the other user's profile
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function update_profile(User $user, User $model) {
        return $user->id === $model->id;
    }
}
