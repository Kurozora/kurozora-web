<?php

namespace App\Notifications;

use App\Http\Resources\NotificationResource;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class NewFollower extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var User $follower */
    private $follower;

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
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', ApnChannel::class];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'user_id'   => $this->follower->id,
            'username'  => $this->follower->username
        ];
    }

    /**
     * Get the APN representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return ApnMessage
     */
    public function toApn($notifiable)
    {
        return ApnMessage::create()
            ->badge(0)
            ->title('New follower')
            ->body($this->follower->username . ' has started following you.');
    }
}
