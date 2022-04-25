<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserLibrary;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserLibraryPolicy
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
     * @param UserLibrary $userLibrary
     * @return Response|bool
     */
    public function view(User $user, UserLibrary $userLibrary): Response|bool
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
        return $user->can('createUserLibrary');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param UserLibrary $userLibrary
     * @return Response|bool
     */
    public function update(User $user, UserLibrary $userLibrary): Response|bool
    {
        return $user->can('updateUserLibrary');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UserLibrary $userLibrary
     * @return Response|bool
     */
    public function delete(User $user, UserLibrary $userLibrary): Response|bool
    {
        return $user->can('deleteUserLibrary');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param UserLibrary $userLibrary
     * @return Response|bool
     */
    public function restore(User $user, UserLibrary $userLibrary): Response|bool
    {
        return $user->can('restoreUserLibrary');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param UserLibrary $userLibrary
     * @return Response|bool
     */
    public function forceDelete(User $user, UserLibrary $userLibrary): Response|bool
    {
        return $user->can('forceDeleteUserLibrary');
    }
}
