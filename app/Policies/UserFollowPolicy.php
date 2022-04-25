<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserFollowPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param UserFollow $userFollow
     * @return Response|bool
     */
    public function view(User $user, UserFollow $userFollow): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->can('createUserFollow');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param UserFollow $userFollow
     * @return Response|bool
     */
    public function update(User $user, UserFollow $userFollow): Response|bool
    {
        return $user->can('updateUserFollow');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UserFollow $userFollow
     * @return Response|bool
     */
    public function delete(User $user, UserFollow $userFollow): Response|bool
    {
        return $user->can('deleteUserFollow');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param UserFollow $userFollow
     * @return Response|bool
     */
    public function restore(User $user, UserFollow $userFollow): Response|bool
    {
        return $user->can('restoreUserFollow');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param UserFollow $userFollow
     * @return Response|bool
     */
    public function forceDelete(User $user, UserFollow $userFollow): Response|bool
    {
        return $user->can('forceDeleteUserFollow');
    }
}
