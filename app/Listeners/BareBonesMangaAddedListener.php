<?php

namespace App\Listeners;

use App\Events\BareBonesMangaAdded;
use App\Jobs\ProcessBareBonesMangaAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;

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
        if (!Redis::set('mal:bb:manga:' . $event->malID, 1, 'EX', 86400, 'NX')) {
            return;
        }

        dispatch(new ProcessBareBonesMangaAdded($event->malID))
            ->delay(now()->addMinutes(5));
    }
}
