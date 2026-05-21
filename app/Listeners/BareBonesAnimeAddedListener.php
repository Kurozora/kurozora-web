<?php

namespace App\Listeners;

use App\Events\BareBonesAnimeAdded;
use App\Jobs\ProcessBareBonesAnimeAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;

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
        if (!Redis::set('mal:bb:anime:' . $event->malID, 1, 'EX', 86400, 'NX')) {
            return;
        }

        dispatch(new ProcessBareBonesAnimeAdded($event->malID))
            ->delay(now()->addMinutes(5));
    }
}
