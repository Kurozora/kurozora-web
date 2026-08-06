<?php

namespace App\Notifications;

use App\Models\Timeout;
use App\Models\User;
use App\Notifications\Concerns\BroadcastsAsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class UserTimedOut extends Notification implements ShouldQueue
{
    use BroadcastsAsNotification,
        Queueable;

    /**
     * The timeout being announced.
     *
     * @var Timeout $issuedTimeout
     */
    private Timeout $issuedTimeout;

    /**
     * Create a new notification instance.
     *
     * @param Timeout $timeout
     */
    public function __construct(Timeout $timeout)
    {
        $this->issuedTimeout = $timeout;
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
        return $this->payload();
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
        return new BroadcastMessage($this->payload());
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
        $body = $this->issuedTimeout->is_permanent
            ? __('Your account has been permanently suspended.')
            : __('Your account has been suspended until :expiry.', [
                'expiry' => $this->issuedTimeout->expires_at?->translatedFormat('j M Y'),
            ]);

        return ApnMessage::create()
            ->title(__('Account Suspended'))
            ->badge($notifiable->unreadNotifications()->count())
            ->body($body)
            ->custom('notification_id', $this->id)
            ->custom('timeout_id', (string) $this->issuedTimeout->id);
    }

    /**
     * Returns the payload shared between the database and broadcast channels.
     *
     * @return array
     */
    private function payload(): array
    {
        return [
            'timeoutID' => (string) $this->issuedTimeout->id,
            'reasonKey' => $this->issuedTimeout->reason_key->value,
            'reasonLabel' => $this->issuedTimeout->reason_key->description,
            'isPermanent' => $this->issuedTimeout->is_permanent,
            'expiresAt' => $this->issuedTimeout->expires_at?->timestamp,
        ];
    }
}
