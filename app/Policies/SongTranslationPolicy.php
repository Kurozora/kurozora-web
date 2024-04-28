<?php

namespace App\Policies;

use App\Models\SongTranslation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SongTranslationPolicy
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
     * @param SongTranslation $songTranslation
     * @return Response|bool
     */
    public function view(User $user, SongTranslation $songTranslation): Response|bool
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
        return $user->can('createSongTranslation');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param SongTranslation $songTranslation
     * @return Response|bool
     */
    public function update(User $user, SongTranslation $songTranslation): Response|bool
    {
        return $user->can('updateSongTranslation');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param SongTranslation $songTranslation
     * @return Response|bool
     */
    public function delete(User $user, SongTranslation $songTranslation): Response|bool
    {
        return $user->can('deleteSongTranslation');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param SongTranslation $songTranslation
     * @return Response|bool
     */
    public function restore(User $user, SongTranslation $songTranslation): Response|bool
    {
        return $user->can('restoreSongTranslation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param SongTranslation $songTranslation
     * @return Response|bool
     */
    public function forceDelete(User $user, SongTranslation $songTranslation): Response|bool
    {
        return $user->can('forceDeleteSongTranslation');
    }
}
