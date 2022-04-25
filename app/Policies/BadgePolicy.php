<?php

namespace App\Policies;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class BadgePolicy
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
     * @param Badge $badge
     * @return Response|bool
     */
    public function view(User $user, Badge $badge): Response|bool
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
        return $user->can('createBadge');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Badge $badge
     * @return Response|bool
     */
    public function update(User $user, Badge $badge): Response|bool
    {
        return $user->can('updateBadge');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Badge $badge
     * @return Response|bool
     */
    public function delete(User $user, Badge $badge): Response|bool
    {
        return $user->can('deleteBadge');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Badge $badge
     * @return Response|bool
     */
    public function restore(User $user, Badge $badge): Response|bool
    {
        return $user->can('restoreBadge');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Badge $badge
     * @return Response|bool
     */
    public function forceDelete(User $user, Badge $badge): Response|bool
    {
        return $user->can('forceDeleteBadge');
    }
}
