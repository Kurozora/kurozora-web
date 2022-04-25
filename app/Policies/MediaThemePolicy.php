<?php

namespace App\Policies;

use App\Models\MediaTheme;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MediaThemePolicy
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
     * @param MediaTheme $mediaTheme
     * @return Response|bool
     */
    public function view(User $user, MediaTheme $mediaTheme): Response|bool
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
        return $user->can('createMediaTheme');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param MediaTheme $mediaTheme
     * @return Response|bool
     */
    public function update(User $user, MediaTheme $mediaTheme): Response|bool
    {
        return $user->can('updateMediaTheme');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param MediaTheme $mediaTheme
     * @return Response|bool
     */
    public function delete(User $user, MediaTheme $mediaTheme): Response|bool
    {
        return $user->can('deleteMediaTheme');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param MediaTheme $mediaTheme
     * @return Response|bool
     */
    public function restore(User $user, MediaTheme $mediaTheme): Response|bool
    {
        return $user->can('restoreMediaTheme');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param MediaTheme $mediaTheme
     * @return Response|bool
     */
    public function forceDelete(User $user, MediaTheme $mediaTheme): Response|bool
    {
        return $user->can('forceDeleteMediaTheme');
    }
}
