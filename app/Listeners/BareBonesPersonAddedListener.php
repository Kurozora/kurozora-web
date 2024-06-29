<?php

namespace App\Listeners;

use App\Events\BareBonesPersonAdded;
use App\Jobs\ProcessBareBonesPersonAdded;
use Illuminate\Contracts\Queue\ShouldQueue;

class BareBonesPersonAddedListener implements ShouldQueue
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
    public function handle(BareBonesPersonAdded $event): void
    {
        // Dispatch the job, but delay it with 5 minutes to
        // lessen the load on the server.
        dispatch(new ProcessBareBonesPersonAdded($event->malID))
            ->delay(now()->addMinutes(5));
    }
}
