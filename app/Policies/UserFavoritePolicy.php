<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserFavoritePolicy
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
     * @param UserFavorite $userFavorite
     * @return Response|bool
     */
    public function view(User $user, UserFavorite $userFavorite): Response|bool
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
        return $user->can('createUserFavorite');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param UserFavorite $userFavorite
     * @return Response|bool
     */
    public function update(User $user, UserFavorite $userFavorite): Response|bool
    {
        return $user->can('updateUserFavorite');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UserFavorite $userFavorite
     * @return Response|bool
     */
    public function delete(User $user, UserFavorite $userFavorite): Response|bool
    {
        return $user->can('deleteUserFavorite');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param UserFavorite $userFavorite
     * @return Response|bool
     */
    public function restore(User $user, UserFavorite $userFavorite): Response|bool
    {
        return $user->can('restoreUserFavorite');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param UserFavorite $userFavorite
     * @return Response|bool
     */
    public function forceDelete(User $user, UserFavorite $userFavorite): Response|bool
    {
        return $user->can('forceDeleteUserFavorite');
    }
}
