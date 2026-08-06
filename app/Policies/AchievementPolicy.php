<?php

namespace App\Policies;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AchievementPolicy
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
     * @param User        $user
     * @param Achievement $achievement
     *
     * @return Response|bool
     */
    public function view(User $user, Achievement $achievement): Response|bool
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
        return $user->can('createAchievement');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User        $user
     * @param Achievement $achievement
     *
     * @return Response|bool
     */
    public function update(User $user, Achievement $achievement): Response|bool
    {
        return $user->can('updateAchievement');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User        $user
     * @param Achievement $achievement
     *
     * @return Response|bool
     */
    public function delete(User $user, Achievement $achievement): Response|bool
    {
        return $user->can('deleteAchievement');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User        $user
     * @param Achievement $achievement
     *
     * @return Response|bool
     */
    public function restore(User $user, Achievement $achievement): Response|bool
    {
        return $user->can('restoreAchievement');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User        $user
     * @param Achievement $achievement
     *
     * @return Response|bool
     */
    public function forceDelete(User $user, Achievement $achievement): Response|bool
    {
        return $user->can('forceDeleteAchievement');
    }
}
