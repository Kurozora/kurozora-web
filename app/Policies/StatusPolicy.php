<?php

namespace App\Policies;

use App\Models\Status;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class StatusPolicy
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
     * @param Status $status
     * @return Response|bool
     */
    public function view(User $user, Status $status): Response|bool
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
        return $user->can('createStatus');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Status $status
     * @return Response|bool
     */
    public function update(User $user, Status $status): Response|bool
    {
        return $user->can('updateStatus');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Status $status
     * @return Response|bool
     */
    public function delete(User $user, Status $status): Response|bool
    {
        return $user->can('deleteStatus');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Status $status
     * @return Response|bool
     */
    public function restore(User $user, Status $status): Response|bool
    {
        return $user->can('restoreStatus');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Status $status
     * @return Response|bool
     */
    public function forceDelete(User $user, Status $status): Response|bool
    {
        return $user->can('forceDeleteStatus');
    }
}
