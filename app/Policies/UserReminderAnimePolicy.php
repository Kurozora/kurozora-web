<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserReminderAnime;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserReminderAnimePolicy
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
     * @param UserReminderAnime $userReminderAnime
     * @return Response|bool
     */
    public function view(User $user, UserReminderAnime $userReminderAnime): Response|bool
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
     * @param UserReminderAnime $userReminderAnime
     * @return Response|bool
     */
    public function update(User $user, UserReminderAnime $userReminderAnime): Response|bool
    {
        return $user->can('updateUserReminderAnime') || $user->id === $userReminderAnime->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UserReminderAnime $userReminderAnime
     * @return Response|bool
     */
    public function delete(User $user, UserReminderAnime $userReminderAnime): Response|bool
    {
        return $user->can('deleteUserReminderAnime') || $user->id === $userReminderAnime->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param UserReminderAnime $userReminderAnime
     * @return Response|bool
     */
    public function restore(User $user, UserReminderAnime $userReminderAnime): Response|bool
    {
        return $user->can('restoreUserReminderAnime');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param UserReminderAnime $userReminderAnime
     * @return Response|bool
     */
    public function forceDelete(User $user, UserReminderAnime $userReminderAnime): Response|bool
    {
        return $user->can('forceDeleteUserReminderAnime');
    }
}
