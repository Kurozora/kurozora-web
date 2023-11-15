<?php

namespace App\Listeners;

use App\Events\BareBonesMangaAdded;
use App\Jobs\ProcessBareBonesMangaAdded;
use Illuminate\Contracts\Queue\ShouldQueue;

class BareBonesMangaAddedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BareBonesMangaAdded $event): void
    {
        // Dispatch the job, but delay it with 5 minutes to
        // lessen the load on the server.
        dispatch(new ProcessBareBonesMangaAdded($event->malID))
            ->delay(now()->addMinutes(5));
    }
}
