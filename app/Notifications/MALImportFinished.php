<?php

namespace App\Notifications;

use App\Enums\MALImportBehavior;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class MALImportFinished extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The results of the import action.
     *
     * @var array $results
     */
    private array $results;

    /**
     * The behavior used when importing.
     *
     * @var MALImportBehavior $behavior
     */
    private MALImportBehavior $behavior;

    /**
     * Create a new notification instance.
     *
     * @param array $results
     * @param MALImportBehavior $behavior
     */
    public function __construct(array $results, MALImportBehavior $behavior)
    {
        $this->results = $results;
        $this->behavior = $behavior;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database', ApnChannel::class];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'successful_count'  => count($this->results['successful']),
            'failure_count'     => count($this->results['failure']),
            'behavior'          => $this->behavior->description
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
            ->title('🤩 MAL Import finished')
            ->badge($notifiable->unreadNotifications()->count())
            ->body('Your MAL import was processed, come check it out!');
    }
}
