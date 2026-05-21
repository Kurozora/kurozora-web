<?php

namespace App\Listeners;

use App\Events\BareBonesProducerAdded;
use App\Jobs\ProcessBareBonesProducerAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;

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
        if (!Redis::set('mal:bb:producer:' . $event->malID, 1, 'EX', 86400, 'NX')) {
            return;
        }

        dispatch(new ProcessBareBonesProducerAdded($event->malID))
            ->delay(now()->addMinutes(5));
    }
}
