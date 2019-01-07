<?php

namespace App\Policies;

use App\User;
use App\UserNotification;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserNotificationPolicy
{
    use HandlesAuthorization;

    const MODEL = UserNotification::class;

    /**
     * Determine whether the user can get the details of a notification
     *
     * @param User $user
     * @param UserNotification $notification
     * @return bool
     */
    public function get_notification(User $user, UserNotification $notification) {
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can delete a notification
     *
     * @param User $user
     * @param UserNotification $notification
     * @return bool
     */
    public function del_notification(User $user, UserNotification $notification) {
        return $user->id === $notification->user_id;
    }
}
