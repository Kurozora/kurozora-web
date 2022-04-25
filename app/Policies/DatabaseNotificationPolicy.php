<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Verifies that a notification was sent to a user, and not some other model.
     *
     * @param DatabaseNotification $databaseNotification
     * @return Response|bool
     */
    private function isNotifyingUser(DatabaseNotification $databaseNotification): Response|bool
    {
        return $databaseNotification->notifiable instanceof User;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param DatabaseNotification $databaseNotification
     * @return Response|bool
     */
    public function view(User $user, DatabaseNotification $databaseNotification): Response|bool
    {
        if (!$this->isNotifyingUser($databaseNotification)) {
            return false;
        }

        return $user->id === (int) $databaseNotification->notifiable->id;
    }

//    /**
//     * Determine whether the user can create models.
//     *
//     * @param User $user
//     * @return Response|bool
//     */
//    public function create(User $user): Response|bool
//    {
//        return $user->can('createDatabaseNotification');
//    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param DatabaseNotification $databaseNotification
     * @return Response|bool
     */
    public function update(User $user, DatabaseNotification $databaseNotification): Response|bool
    {
        if (!$this->isNotifyingUser($databaseNotification)) {
            return false;
        }

        return $user->can('updateDatabaseNotification') || (int) $user->id === $databaseNotification->notifiable->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param DatabaseNotification $databaseNotification
     * @return Response|bool
     */
    public function delete(User $user, DatabaseNotification $databaseNotification): Response|bool
    {
        if (!$this->isNotifyingUser($databaseNotification)) {
            return false;
        }

        return $user->can('deleteDatabaseNotification') || $user->id === (int) $databaseNotification->notifiable->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param DatabaseNotification $databaseNotification
     * @return Response|bool
     */
    public function restore(User $user, DatabaseNotification $databaseNotification): Response|bool
    {
        return $user->can('restoreDatabaseNotification');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param DatabaseNotification $databaseNotification
     * @return Response|bool
     */
    public function forceDelete(User $user, DatabaseNotification $databaseNotification): Response|bool
    {
        return $user->can('forceDeleteDatabaseNotification');
    }
}
