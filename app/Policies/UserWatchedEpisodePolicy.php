<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserWatchedEpisode;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserWatchedEpisodePolicy
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
     * @param UserWatchedEpisode $userWatchedEpisode
     * @return Response|bool
     */
    public function view(User $user, UserWatchedEpisode $userWatchedEpisode): Response|bool
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
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param UserWatchedEpisode $userWatchedEpisode
     * @return Response|bool
     */
    public function update(User $user, UserWatchedEpisode $userWatchedEpisode): Response|bool
    {
        return $user->can('updateUserWatchedEpisode') || $user->id === $userWatchedEpisode->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UserWatchedEpisode $userWatchedEpisode
     * @return Response|bool
     */
    public function delete(User $user, UserWatchedEpisode $userWatchedEpisode): Response|bool
    {
        return $user->can('deleteUserWatchedEpisode') || $user->id === $userWatchedEpisode->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param UserWatchedEpisode $userWatchedEpisode
     * @return Response|bool
     */
    public function restore(User $user, UserWatchedEpisode $userWatchedEpisode): Response|bool
    {
        return $user->can('restoreUserWatchedEpisode');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param UserWatchedEpisode $userWatchedEpisode
     * @return Response|bool
     */
    public function forceDelete(User $user, UserWatchedEpisode $userWatchedEpisode): Response|bool
    {
        return $user->can('forceDeleteUserWatchedEpisode');
    }
}
