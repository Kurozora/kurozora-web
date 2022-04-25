<?php

namespace App\Policies;

use App\Models\StaffRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class StaffRolePolicy
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
     * @param StaffRole $staffRole
     * @return Response|bool
     */
    public function view(User $user, StaffRole $staffRole): Response|bool
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
        return $user->can('createStaffRole');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param StaffRole $staffRole
     * @return Response|bool
     */
    public function update(User $user, StaffRole $staffRole): Response|bool
    {
        return $user->can('updateStaffRole');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param StaffRole $staffRole
     * @return Response|bool
     */
    public function delete(User $user, StaffRole $staffRole): Response|bool
    {
        return $user->can('deleteStaffRole');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param StaffRole $staffRole
     * @return Response|bool
     */
    public function restore(User $user, StaffRole $staffRole): Response|bool
    {
        return $user->can('restoreStaffRole');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param StaffRole $staffRole
     * @return Response|bool
     */
    public function forceDelete(User $user, StaffRole $staffRole): Response|bool
    {
        return $user->can('forceDeleteStaffRole');
    }
}
