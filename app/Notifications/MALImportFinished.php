<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class MALImportFinished extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var array $results */
    private $results;

    /** @var string $behavior */
    private $behavior;

    /**
     * Create a new notification instance.
     *
     * @param array $results
     * @param string $behavior
     */
    public function __construct($results, $behavior)
    {
        $this->results = $results;
        $this->behavior = $behavior;
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
            'successful_count'  => count($this->results['successful']),
            'failure_count'     => count($this->results['failure']),
            'behavior'          => $this->behavior
        ];
    }

    /**
     * Get the APN representation of the notification.
     *
     * @param  User  $notifiable
     * @return ApnMessage
     */
    public function toApn($notifiable)
    {
        return ApnMessage::create()
            ->title('🤩 MAL Import finished')
            ->badge($notifiable->unreadNotifications()->count())
            ->body('Your MAL import was processed, come check it out!');
    }
}
