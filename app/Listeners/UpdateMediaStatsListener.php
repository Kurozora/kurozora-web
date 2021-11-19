<?php

namespace App\Listeners;

use App\Events\UserLibraryUpdatedEvent;
use App\Jobs\UpdateMediaStatsJob;

class UpdateMediaStatsListener
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
    public function handle(UserLibraryUpdatedEvent $event)
    {
        dispatch(new UpdateMediaStatsJob($event->userLibrary));
    }
}
