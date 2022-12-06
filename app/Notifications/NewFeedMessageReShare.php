<?php

namespace App\Notifications;

use App\Enums\MediaCollection;
use App\Models\FeedMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class NewFeedMessageReShare extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The message that was replied to.
     *
     * @var FeedMessage $feedMessage
     */
    private FeedMessage $feedMessage;

    /**
     * Create a new notification instance.
     *
     * @param FeedMessage $feedMessage
     */
    public function __construct(FeedMessage $feedMessage)
    {
        $this->feedMessage = $feedMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database', ApnChannel::class];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'userID'            => $this->feedMessage->user->id,
            'username'          => $this->feedMessage->user->username,
            'profileImageURL'   => $this->feedMessage->user->getFirstMediaFullUrl(MediaCollection::Profile()),
            'feedMessageID'     => $this->feedMessage->id,
        ];
    }

    /**
     * Get the APN representation of the notification.
     *
     * @param User $notifiable
     * @return ApnMessage
     */
    public function toApn(User $notifiable): ApnMessage
    {
        return ApnMessage::create()
            ->title($this->feedMessage->user->username . ' ReShared Your Message')
            ->badge($notifiable->unreadNotifications()->count())
            ->body($this->feedMessage->content);
    }
}
