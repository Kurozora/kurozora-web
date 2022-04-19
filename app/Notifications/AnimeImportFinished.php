<?php

namespace App\Notifications;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class AnimeImportFinished extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The results of the import action.
     *
     * @var array $results
     */
    private array $results;

    /**
     * The service used when importing.
     *
     * @var ImportService $service
     */
    private ImportService $service;

    /**
     * The behavior used when importing.
     *
     * @var ImportBehavior $behavior
     */
    private ImportBehavior $behavior;

    /**
     * Create a new notification instance.
     *
     * @param array $results
     * @param ImportService $service
     * @param ImportBehavior $behavior
     */
    public function __construct(array $results, ImportService $service, ImportBehavior $behavior)
    {
        $this->results = $results;
        $this->service = $service;
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
            'behavior'          => $this->behavior->description,
            'service'           => $this->service->description,
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
        $serviceName = $this->service->description;

        return ApnMessage::create()
            ->title('🤩 ' . $serviceName . ' Import finished')
            ->badge($notifiable->unreadNotifications()->count())
            ->body('Your ' . $serviceName . ' anime import was processed, come check it out!');
    }
}
