<?php

namespace App\Listeners;

use App\Events\UserLibraryCreatedEvent;
use App\Jobs\UpdateMediaStatsJob;

class CreateMediaStatsListener
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
     * @param UserLibraryCreatedEvent $event
     * @return void
     */
    public function handle(UserLibraryCreatedEvent $event)
    {
        dispatch(new UpdateMediaStatsJob($event->userLibrary));
    }
}
