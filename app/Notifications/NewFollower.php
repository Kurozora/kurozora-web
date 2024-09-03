<?php

namespace App\Notifications;

use App\Enums\MediaCollection;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class NewFollower extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The new user following the user receiving this notification.
     *
     * @var User $follower
     */
    private User $follower;

    /**
     * Create a new notification instance.
     *
     * @param User $follower
     */
    public function __construct(User $follower)
    {
        $this->follower = $follower;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database', ApnChannel::class];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'userID' => (string) $this->follower->id,
            'username' => $this->follower->username,
            'profileImageURL' => $this->follower->getFirstMediaFullUrl(MediaCollection::Profile())
        ];
    }

    /**
     * Get the APN representation of the notification.
     *
     * @param User $notifiable
     *
     * @return ApnMessage
     */
    public function toApn(User $notifiable): ApnMessage
    {
        return ApnMessage::create()
            ->title(__('New follower'))
            ->badge($notifiable->unreadNotifications()->count())
            ->body(__(':x followed you.', ['x' => $this->follower->username]));
    }
}
