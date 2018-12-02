<?php

namespace App\Listeners;

use App\Events\NewUserSessionEvent;
use App\UserNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Broadcast;

class SendSessionNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewUserSessionEvent  $event
     * @return void
     */
    public function handle(NewUserSessionEvent $event)
    {
        // Insert a new user notification
        UserNotification::create([
            'user_id'   => $event->userID,
            'type'      => UserNotification::TYPE_NEW_SESSION,
            'data'      => json_encode([
                'ip'            => $event->ipAddress,
                'session_id'    => $event->userID
            ])
        ]);
    }
}
