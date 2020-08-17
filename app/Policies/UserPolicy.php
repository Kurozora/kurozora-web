<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can get to another user's anime favorites.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function get_anime_favorites(User $user, User $model): bool
    {
        return true;
        // return $user->id === $model->id;
    }

    /**
     * Determine whether the user can update the other user's profile.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function update_profile(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can get another user's sessions.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function get_sessions(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can follow another user.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function follow(User $user, User $model): bool
    {
        return $user->id !== $model->id;
    }
}
