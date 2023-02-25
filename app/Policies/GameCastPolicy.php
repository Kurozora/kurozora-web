<?php

namespace App\Policies;

use App\Models\GameCast;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class GameCastPolicy
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
     * @param GameCast $animeCast
     * @return Response|bool
     */
    public function view(User $user, GameCast $animeCast): Response|bool
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
        return $user->can('createGameCast');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param GameCast $animeCast
     * @return Response|bool
     */
    public function update(User $user, GameCast $animeCast): Response|bool
    {
        return $user->can('updateGameCast');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param GameCast $animeCast
     * @return Response|bool
     */
    public function delete(User $user, GameCast $animeCast): Response|bool
    {
        return $user->can('deleteGameCast');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param GameCast $animeCast
     * @return Response|bool
     */
    public function restore(User $user, GameCast $animeCast): Response|bool
    {
        return $user->can('restoreGameCast');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param GameCast $animeCast
     * @return Response|bool
     */
    public function forceDelete(User $user, GameCast $animeCast): Response|bool
    {
        return $user->can('forceDeleteGameCast');
    }
}
