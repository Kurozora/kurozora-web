<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserReminder;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserReminderPolicy
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
     * @param User         $user
     * @param UserReminder $userReminder
     *
     * @return Response|bool
     */
    public function view(User $user, UserReminder $userReminder): Response|bool
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
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User         $user
     * @param UserReminder $userReminder
     *
     * @return Response|bool
     */
    public function update(User $user, UserReminder $userReminder): Response|bool
    {
        return $user->can('updateUserReminder') || $user->id === $userReminder->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User         $user
     * @param UserReminder $userReminder
     *
     * @return Response|bool
     */
    public function delete(User $user, UserReminder $userReminder): Response|bool
    {
        return $user->can('deleteUserReminder') || $user->id === $userReminder->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User         $user
     * @param UserReminder $userReminder
     *
     * @return Response|bool
     */
    public function restore(User $user, UserReminder $userReminder): Response|bool
    {
        return $user->can('restoreUserReminder');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User         $user
     * @param UserReminder $userReminder
     *
     * @return Response|bool
     */
    public function forceDelete(User $user, UserReminder $userReminder): Response|bool
    {
        return $user->can('forceDeleteUserReminder');
    }
}
