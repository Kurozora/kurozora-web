<?php

namespace App\Policies;

use App\Models\AnimeCast;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AnimeCastPolicy
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
     * @param AnimeCast $animeCast
     * @return Response|bool
     */
    public function view(User $user, AnimeCast $animeCast): Response|bool
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
        return $user->can('createAnimeCast');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param AnimeCast $animeCast
     * @return Response|bool
     */
    public function update(User $user, AnimeCast $animeCast): Response|bool
    {
        return $user->can('updateAnimeCast');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param AnimeCast $animeCast
     * @return Response|bool
     */
    public function delete(User $user, AnimeCast $animeCast): Response|bool
    {
        return $user->can('deleteAnimeCast');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param AnimeCast $animeCast
     * @return Response|bool
     */
    public function restore(User $user, AnimeCast $animeCast): Response|bool
    {
        return $user->can('restoreAnimeCast');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param AnimeCast $animeCast
     * @return Response|bool
     */
    public function forceDelete(User $user, AnimeCast $animeCast): Response|bool
    {
        return $user->can('forceDeleteAnimeCast');
    }
}
