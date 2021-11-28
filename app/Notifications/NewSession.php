<?php

namespace App\Notifications;

use App\Models\SessionAttribute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewSession extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The session object.
     *
     * @var SessionAttribute $sessionA
     */
    private SessionAttribute $sessionAttribute;

    /**
     * Create a new notification instance.
     *
     * @param SessionAttribute $sessionAttribute
     */
    public function __construct(SessionAttribute $sessionAttribute)
    {
        $this->sessionAttribute = $sessionAttribute;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
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
            'sessionID' => $this->sessionAttribute->id,
            'ipAddress' => $this->sessionAttribute->ip_address,
        ];
    }
}
