<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Notifications\Notification as IlluminateNotification;

class NotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Verifies that a notification was sent to a user, and not some other model.
     *
     * @param Notification|IlluminateNotification $notification
     * @return Response|bool
     */
    private function isNotifyingUser(Notification|IlluminateNotification $notification): Response|bool
    {
        return $notification->notifiable instanceof User;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Notification|IlluminateNotification $notification
     * @return Response|bool
     */
    public function view(User $user, Notification|IlluminateNotification $notification): Response|bool
    {
        if (!$this->isNotifyingUser($notification)) {
            return false;
        }

        return $user->id === (int) $notification->notifiable->id;
    }

//    /**
//     * Determine whether the user can create models.
//     *
//     * @param User $user
//     * @return Response|bool
//     */
//    public function create(User $user): Response|bool
//    {
//        return $user->can('createNotification');
//    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Notification|IlluminateNotification $notification
     * @return Response|bool
     */
    public function update(User $user, Notification|IlluminateNotification $notification): Response|bool
    {
        if (!$this->isNotifyingUser($notification)) {
            return false;
        }

        return $user->can('updateNotification') || $user->id === (int) $notification->notifiable->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Notification|IlluminateNotification $notification
     * @return Response|bool
     */
    public function delete(User $user, Notification|IlluminateNotification $notification): Response|bool
    {
        if (!$this->isNotifyingUser($notification)) {
            return false;
        }

        return $user->can('deleteNotification') || $user->id === (int) $notification->notifiable->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Notification|IlluminateNotification $notification
     * @return Response|bool
     */
    public function restore(User $user, Notification|IlluminateNotification $notification): Response|bool
    {
        return $user->can('restoreNotification');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Notification|IlluminateNotification $notification
     * @return Response|bool
     */
    public function forceDelete(User $user, Notification|IlluminateNotification $notification): Response|bool
    {
        return $user->can('forceDeleteNotification');
    }
}
