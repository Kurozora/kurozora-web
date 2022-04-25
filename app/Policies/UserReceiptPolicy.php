<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserReceipt;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserReceiptPolicy
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
     * @param UserReceipt $userReceipt
     * @return Response|bool
     */
    public function view(User $user, UserReceipt $userReceipt): Response|bool
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
        return $user->can('createUserReceipt');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param UserReceipt $userReceipt
     * @return Response|bool
     */
    public function update(User $user, UserReceipt $userReceipt): Response|bool
    {
        return $user->can('updateUserReceipt');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UserReceipt $userReceipt
     * @return Response|bool
     */
    public function delete(User $user, UserReceipt $userReceipt): Response|bool
    {
        return $user->can('deleteUserReceipt');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param UserReceipt $userReceipt
     * @return Response|bool
     */
    public function restore(User $user, UserReceipt $userReceipt): Response|bool
    {
        return $user->can('restoreUserReceipt');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param UserReceipt $userReceipt
     * @return Response|bool
     */
    public function forceDelete(User $user, UserReceipt $userReceipt): Response|bool
    {
        return $user->can('forceDeleteUserReceipt');
    }
}
