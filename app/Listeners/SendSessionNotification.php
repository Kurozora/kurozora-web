<?php

namespace App\Listeners;

use App\Events\NewUserSessionEvent;
use App\UserNotification;

class SendSessionNotification
{
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
            'user_id'   => $event->sessionObj->user_id,
            'type'      => UserNotification::TYPE_NEW_SESSION,
            'data'      => json_encode([
                'ip'            => $event->sessionObj->ip,
                'session_id'    => $event->sessionObj->id
            ])
        ]);
    }
}
