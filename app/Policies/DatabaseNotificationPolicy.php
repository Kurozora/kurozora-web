<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can get the details of a notification.
     *
     * @param User $user
     * @param DatabaseNotification $notification
     * @return bool
     */
    public function get_notification(User $user, DatabaseNotification $notification): bool
    {
        if(!$this->isUserNotification($notification)) return false;

        return $user->id === $notification->notifiable->id;
    }

    /**
     * Determine whether the user can delete a notification.
     *
     * @param User $user
     * @param DatabaseNotification $notification
     * @return bool
     */
    public function del_notification(User $user, DatabaseNotification $notification): bool
    {
        if(!$this->isUserNotification($notification)) return false;

        return $user->id === $notification->notifiable->id;
    }

    /**
     * Verifies that a notification was sent to a user, and not some other model.
     *
     * @param DatabaseNotification $notification
     * @return bool
     */
    private function isUserNotification(DatabaseNotification $notification): bool
    {
        return $notification->notifiable instanceof User;
    }
}
