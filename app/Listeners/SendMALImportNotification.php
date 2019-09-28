<?php

namespace App\Listeners;

use App\Events\MALImportFinished;
use App\UserNotification;

class SendMALImportNotification
{
    /**
     * Handle the event.
     *
     * @param MALImportFinished $event
     * @return void
     */
    public function handle(MALImportFinished $event)
    {
        // Insert a new user notification
        UserNotification::create([
            'user_id'   => $event->user->id,
            'type'      => UserNotification::TYPE_MAL_IMPORT_UPDATE,
            'data'      => json_encode([
                'successful_count'  => count($event->results['successful']),
                'failure_count'     => count($event->results['failure']),
                'behavior'          => $event->behavior
            ])
        ]);
    }
}
