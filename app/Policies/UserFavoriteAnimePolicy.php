<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserFavoriteAnime;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserFavoriteAnimePolicy
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
     * @param UserFavoriteAnime $userFavoriteAnime
     * @return Response|bool
     */
    public function view(User $user, UserFavoriteAnime $userFavoriteAnime): Response|bool
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
        return $user->can('createUserFavoriteAnime');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param UserFavoriteAnime $userFavoriteAnime
     * @return Response|bool
     */
    public function update(User $user, UserFavoriteAnime $userFavoriteAnime): Response|bool
    {
        return $user->can('updateUserFavoriteAnime');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UserFavoriteAnime $userFavoriteAnime
     * @return Response|bool
     */
    public function delete(User $user, UserFavoriteAnime $userFavoriteAnime): Response|bool
    {
        return $user->can('deleteUserFavoriteAnime');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param UserFavoriteAnime $userFavoriteAnime
     * @return Response|bool
     */
    public function restore(User $user, UserFavoriteAnime $userFavoriteAnime): Response|bool
    {
        return $user->can('restoreUserFavoriteAnime');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param UserFavoriteAnime $userFavoriteAnime
     * @return Response|bool
     */
    public function forceDelete(User $user, UserFavoriteAnime $userFavoriteAnime): Response|bool
    {
        return $user->can('forceDeleteUserFavoriteAnime');
    }
}
