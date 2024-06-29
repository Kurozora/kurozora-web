<?php

namespace App\Listeners;

use App\Events\BareBonesCharacterAdded;
use App\Jobs\ProcessBareBonesCharacterAdded;
use Illuminate\Contracts\Queue\ShouldQueue;

class BareBonesCharacterAddedListener implements ShouldQueue
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
    public function handle(BareBonesCharacterAdded $event): void
    {
        // Dispatch the job, but delay it with 5 minutes to
        // lessen the load on the server.
        dispatch(new ProcessBareBonesCharacterAdded($event->malID))
            ->delay(now()->addMinutes(5));
    }
}
