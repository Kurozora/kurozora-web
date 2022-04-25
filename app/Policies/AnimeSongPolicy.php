<?php

namespace App\Policies;

use App\Models\AnimeSong;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AnimeSongPolicy
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
     * @param AnimeSong $animeSong
     * @return Response|bool
     */
    public function view(User $user, AnimeSong $animeSong): Response|bool
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
        return $user->can('createAnimeSong');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param AnimeSong $animeSong
     * @return Response|bool
     */
    public function update(User $user, AnimeSong $animeSong): Response|bool
    {
        return $user->can('updateAnimeSong');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param AnimeSong $animeSong
     * @return Response|bool
     */
    public function delete(User $user, AnimeSong $animeSong): Response|bool
    {
        return $user->can('deleteAnimeSong');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param AnimeSong $animeSong
     * @return Response|bool
     */
    public function restore(User $user, AnimeSong $animeSong): Response|bool
    {
        return $user->can('restoreAnimeSong');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param AnimeSong $animeSong
     * @return Response|bool
     */
    public function forceDelete(User $user, AnimeSong $animeSong): Response|bool
    {
        return $user->can('forceDeleteAnimeSong');
    }
}
