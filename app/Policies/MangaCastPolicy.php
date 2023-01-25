<?php

namespace App\Policies;

use App\Models\MangaCast;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MangaCastPolicy
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
     * @param MangaCast $mangaCast
     * @return Response|bool
     */
    public function view(User $user, MangaCast $mangaCast): Response|bool
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
        return $user->can('createMangaCast');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param MangaCast $mangaCast
     * @return Response|bool
     */
    public function update(User $user, MangaCast $mangaCast): Response|bool
    {
        return $user->can('updateMangaCast');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param MangaCast $mangaCast
     * @return Response|bool
     */
    public function delete(User $user, MangaCast $mangaCast): Response|bool
    {
        return $user->can('deleteMangaCast');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param MangaCast $mangaCast
     * @return Response|bool
     */
    public function restore(User $user, MangaCast $mangaCast): Response|bool
    {
        return $user->can('restoreMangaCast');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param MangaCast $mangaCast
     * @return Response|bool
     */
    public function forceDelete(User $user, MangaCast $mangaCast): Response|bool
    {
        return $user->can('forceDeleteMangaCast');
    }
}
