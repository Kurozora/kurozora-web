<?php

namespace App\Policies;

use App\Models\CastRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CastRolePolicy
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
     * @param CastRole $castRole
     * @return Response|bool
     */
    public function view(User $user, CastRole $castRole): Response|bool
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
        return $user->can('createCastRole');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param CastRole $castRole
     * @return Response|bool
     */
    public function update(User $user, CastRole $castRole): Response|bool
    {
        return $user->can('updateCastRole');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param CastRole $castRole
     * @return Response|bool
     */
    public function delete(User $user, CastRole $castRole): Response|bool
    {
        return $user->can('deleteCastRole');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param CastRole $castRole
     * @return Response|bool
     */
    public function restore(User $user, CastRole $castRole): Response|bool
    {
        return $user->can('restoreCastRole');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param CastRole $castRole
     * @return Response|bool
     */
    public function forceDelete(User $user, CastRole $castRole): Response|bool
    {
        return $user->can('forceDeleteCastRole');
    }
}
