<?php

namespace App\Notifications;

use App\Enums\ImportBehavior;
use App\Models\User;
use App\Notifications\Concerns\BroadcastsAsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class LocalLibraryImportFinished extends Notification implements ShouldQueue
{
    use BroadcastsAsNotification,
        Queueable;

    /**
     * The results of the import action.
     *
     * @var array $results
     */
    private array $results;

    /**
     * The behavior used when importing.
     *
     * @var ImportBehavior $behavior
     */
    private ImportBehavior $behavior;

    /**
     * Create a new notification instance.
     *
     * @param array          $results
     * @param ImportBehavior $behavior
     */
    public function __construct(array $results, ImportBehavior $behavior)
    {
        $this->results = $results;
        $this->behavior = $behavior;
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
        return ['database', 'broadcast', ApnChannel::class];
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
            'successful_count' => count($this->results['successful']),
            'failure_count' => count($this->results['failure']),
            'behavior' => $this->behavior->description,
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return BroadcastMessage
     */
    public function toBroadcast(mixed $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'successful_count' => count($this->results['successful']),
            'failure_count' => count($this->results['failure']),
            'behavior' => $this->behavior->description,
        ]);
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
            ->title('🤩 Local Library import finished')
            ->badge($notifiable->unreadNotifications()->count())
            ->body('Your Local Library import was processed. Come check it out!')
            ->custom('notification_id', $this->id);
    }
}
