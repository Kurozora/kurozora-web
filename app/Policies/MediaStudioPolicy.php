<?php

namespace App\Policies;

use App\Models\MediaStudio;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MediaStudioPolicy
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
     * @param MediaStudio $mediaStudio
     * @return Response|bool
     */
    public function view(User $user, MediaStudio $mediaStudio): Response|bool
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
        return $user->can('createMediaStudio');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param MediaStudio $mediaStudio
     * @return Response|bool
     */
    public function update(User $user, MediaStudio $mediaStudio): Response|bool
    {
        return $user->can('updateMediaStudio');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param MediaStudio $mediaStudio
     * @return Response|bool
     */
    public function delete(User $user, MediaStudio $mediaStudio): Response|bool
    {
        return $user->can('deleteMediaStudio');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param MediaStudio $mediaStudio
     * @return Response|bool
     */
    public function restore(User $user, MediaStudio $mediaStudio): Response|bool
    {
        return $user->can('restoreMediaStudio');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param MediaStudio $mediaStudio
     * @return Response|bool
     */
    public function forceDelete(User $user, MediaStudio $mediaStudio): Response|bool
    {
        return $user->can('forceDeleteMediaStudio');
    }
}
