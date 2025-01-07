<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserBlockPolicy
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
     * @param UserBlock $userBlock
     * @return Response|bool
     */
    public function view(User $user, UserBlock $userBlock): Response|bool
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
        return $user->can('createUserBlock');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param UserBlock $userBlock
     * @return Response|bool
     */
    public function update(User $user, UserBlock $userBlock): Response|bool
    {
        return $user->can('updateUserBlock');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UserBlock $userBlock
     * @return Response|bool
     */
    public function delete(User $user, UserBlock $userBlock): Response|bool
    {
        return $user->can('deleteUserBlock');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param UserBlock $userBlock
     * @return Response|bool
     */
    public function restore(User $user, UserBlock $userBlock): Response|bool
    {
        return $user->can('restoreUserBlock');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param UserBlock $userBlock
     * @return Response|bool
     */
    public function forceDelete(User $user, UserBlock $userBlock): Response|bool
    {
        return $user->can('forceDeleteUserBlock');
    }
}
