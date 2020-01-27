<?php

namespace App\Notifications;

use App\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewSession extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var string $ip */
    private $ip;

    /** @var Session $session */
    private $session;

    /**
     * Create a new notification instance.
     *
     * @param string $ip
     * @param Session $session
     */
    public function __construct($ip, $session)
    {
        $this->ip = $ip;
        $this->session = $session;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
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
            'ip'            => $this->ip,
            'session_id'    => $this->session->id
        ];
    }
}
