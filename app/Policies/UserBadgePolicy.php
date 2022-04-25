<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserBadgePolicy
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
     * @param UserBadge $userBadge
     * @return Response|bool
     */
    public function view(User $user, UserBadge $userBadge): Response|bool
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
        return $user->can('createUserBadge');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param UserBadge $userBadge
     * @return Response|bool
     */
    public function update(User $user, UserBadge $userBadge): Response|bool
    {
        return $user->can('updateUserBadge');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UserBadge $userBadge
     * @return Response|bool
     */
    public function delete(User $user, UserBadge $userBadge): Response|bool
    {
        return $user->can('deleteUserBadge');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param UserBadge $userBadge
     * @return Response|bool
     */
    public function restore(User $user, UserBadge $userBadge): Response|bool
    {
        return $user->can('restoreUserBadge');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param UserBadge $userBadge
     * @return Response|bool
     */
    public function forceDelete(User $user, UserBadge $userBadge): Response|bool
    {
        return $user->can('forceDeleteUserBadge');
    }
}
