<?php

namespace App\Listeners;

use App\Events\BareBonesAnimeAdded;
use App\Jobs\ProcessBareBonesAnimeAdded;
use Illuminate\Contracts\Queue\ShouldQueue;

class BareBonesAnimeAddedListener implements ShouldQueue
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
     */
    public function handle(BareBonesAnimeAdded $event): void
    {
        // Dispatch the job, but delay it with 5 minutes to
        // lessen the load on the server.
        dispatch(new ProcessBareBonesAnimeAdded($event->malID))
            ->delay(now()->addMinutes(5));
    }
}
