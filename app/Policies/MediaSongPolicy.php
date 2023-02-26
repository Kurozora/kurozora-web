<?php

namespace App\Policies;

use App\Models\MediaSong;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MediaSongPolicy
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
     * @param MediaSong $mediaSong
     * @return Response|bool
     */
    public function view(User $user, MediaSong $mediaSong): Response|bool
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
        return $user->can('createMediaSong');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param MediaSong $mediaSong
     * @return Response|bool
     */
    public function update(User $user, MediaSong $mediaSong): Response|bool
    {
        return $user->can('updateMediaSong');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param MediaSong $mediaSong
     * @return Response|bool
     */
    public function delete(User $user, MediaSong $mediaSong): Response|bool
    {
        return $user->can('deleteMediaSong');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param MediaSong $mediaSong
     * @return Response|bool
     */
    public function restore(User $user, MediaSong $mediaSong): Response|bool
    {
        return $user->can('restoreMediaSong');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param MediaSong $mediaSong
     * @return Response|bool
     */
    public function forceDelete(User $user, MediaSong $mediaSong): Response|bool
    {
        return $user->can('forceDeleteMediaSong');
    }
}
