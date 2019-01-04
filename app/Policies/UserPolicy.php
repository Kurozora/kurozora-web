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

    /**
     * Determine whether the user can authenticate into another user's Pusher channel
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function authenticate_pusher_channel(User $user, User $model) {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can get another user's sessions
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function get_sessions(User $user, User $model) {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can get another user's notifications
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function get_notifications(User $user, User $model) {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can get another user's library
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function get_library(User $user, User $model) {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can add to another user's library
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function add_to_library(User $user, User $model) {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete from another user's library
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function del_from_library(User $user, User $model) {
        return $user->id === $model->id;
    }
}
