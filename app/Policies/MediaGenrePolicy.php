<?php

namespace App\Policies;

use App\Models\MediaGenre;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MediaGenrePolicy
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
     * @param MediaGenre $mediaGenre
     * @return Response|bool
     */
    public function view(User $user, MediaGenre $mediaGenre): Response|bool
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
        return $user->can('createMediaGenre');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param MediaGenre $mediaGenre
     * @return Response|bool
     */
    public function update(User $user, MediaGenre $mediaGenre): Response|bool
    {
        return $user->can('updateMediaGenre');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param MediaGenre $mediaGenre
     * @return Response|bool
     */
    public function delete(User $user, MediaGenre $mediaGenre): Response|bool
    {
        return $user->can('deleteMediaGenre');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param MediaGenre $mediaGenre
     * @return Response|bool
     */
    public function restore(User $user, MediaGenre $mediaGenre): Response|bool
    {
        return $user->can('restoreMediaGenre');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param MediaGenre $mediaGenre
     * @return Response|bool
     */
    public function forceDelete(User $user, MediaGenre $mediaGenre): Response|bool
    {
        return $user->can('forceDeleteMediaGenre');
    }
}
