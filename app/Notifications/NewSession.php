<?php

namespace App\Notifications;

use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewSession extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The session object.
     *
     * @var Session $session
     */
    private Session $session;

    /**
     * Create a new notification instance.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
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
            'sessionID' => $this->session->id,
            'ipAddress' => $this->session->ip_address,
        ];
    }
}
