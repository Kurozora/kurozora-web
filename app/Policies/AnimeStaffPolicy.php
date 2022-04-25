<?php

namespace App\Policies;

use App\Models\AnimeStaff;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AnimeStaffPolicy
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
     * @param AnimeStaff $animeStaff
     * @return Response|bool
     */
    public function view(User $user, AnimeStaff $animeStaff): Response|bool
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
        return $user->can('createAnimeStaff');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param AnimeStaff $animeStaff
     * @return Response|bool
     */
    public function update(User $user, AnimeStaff $animeStaff): Response|bool
    {
        return $user->can('updateAnimeStaff');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param AnimeStaff $animeStaff
     * @return Response|bool
     */
    public function delete(User $user, AnimeStaff $animeStaff): Response|bool
    {
        return $user->can('deleteAnimeStaff');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param AnimeStaff $animeStaff
     * @return Response|bool
     */
    public function restore(User $user, AnimeStaff $animeStaff): Response|bool
    {
        return $user->can('restoreAnimeStaff');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param AnimeStaff $animeStaff
     * @return Response|bool
     */
    public function forceDelete(User $user, AnimeStaff $animeStaff): Response|bool
    {
        return $user->can('forceDeleteAnimeStaff');
    }
}
