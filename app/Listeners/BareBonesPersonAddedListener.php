<?php

namespace App\Listeners;

use App\Events\BareBonesPersonAdded;
use App\Jobs\ProcessBareBonesPersonAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;

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
        if (!Redis::set('mal:bb:person:' . $event->malID, 1, 'EX', 86400, 'NX')) {
            return;
        }

        dispatch(new ProcessBareBonesPersonAdded($event->malID))
            ->delay(now()->addMinutes(5));
    }
}
