<?php

namespace App\Listeners;

use App\Events\BareBonesProducerAdded;
use App\Jobs\ProcessBareBonesProducerAdded;
use Illuminate\Contracts\Queue\ShouldQueue;

class BareBonesProducerAddedListener implements ShouldQueue
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
    public function handle(BareBonesProducerAdded $event): void
    {
        // Dispatch the job, but delay it with 5 minutes to
        // lessen the load on the server.
        dispatch(new ProcessBareBonesProducerAdded($event->malID))
            ->delay(now()->addMinutes(5));
    }
}

