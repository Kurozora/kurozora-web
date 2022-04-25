<?php

namespace App\Policies;

use App\Models\Song;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SongPolicy
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
     * @param Song $song
     * @return Response|bool
     */
    public function view(User $user, Song $song): Response|bool
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
        return $user->can('createSong');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Song $song
     * @return Response|bool
     */
    public function update(User $user, Song $song): Response|bool
    {
        return $user->can('updateSong');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Song $song
     * @return Response|bool
     */
    public function delete(User $user, Song $song): Response|bool
    {
        return $user->can('deleteSong');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Song $song
     * @return Response|bool
     */
    public function restore(User $user, Song $song): Response|bool
    {
        return $user->can('restoreSong');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Song $song
     * @return Response|bool
     */
    public function forceDelete(User $user, Song $song): Response|bool
    {
        return $user->can('forceDeleteSong');
    }
}
