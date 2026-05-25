<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserAchievementPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User            $user
     * @param UserAchievement $userAchievement
     *
     * @return Response|bool
     */
    public function view(User $user, UserAchievement $userAchievement): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->can('createUserAchievement');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User            $user
     * @param UserAchievement $userAchievement
     *
     * @return Response|bool
     */
    public function update(User $user, UserAchievement $userAchievement): Response|bool
    {
        return $user->can('updateUserAchievement');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User            $user
     * @param UserAchievement $userAchievement
     *
     * @return Response|bool
     */
    public function delete(User $user, UserAchievement $userAchievement): Response|bool
    {
        return $user->can('deleteUserAchievement');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User            $user
     * @param UserAchievement $userAchievement
     *
     * @return Response|bool
     */
    public function restore(User $user, UserAchievement $userAchievement): Response|bool
    {
        return $user->can('restoreUserAchievement');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User            $user
     * @param UserAchievement $userAchievement
     *
     * @return Response|bool
     */
    public function forceDelete(User $user, UserAchievement $userAchievement): Response|bool
    {
        return $user->can('forceDeleteUserAchievement');
    }
}
