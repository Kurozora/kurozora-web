<?php

namespace App\Listeners;

use App\Events\BareBonesGameAdded;
use App\Jobs\ProcessBareBonesGameAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;

class BareBonesGameAddedListener implements ShouldQueue
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
    public function handle(BareBonesGameAdded $event): void
    {
        if (!Redis::set('igdb:bb:game:' . $event->slug, 1, 'EX', 86400, 'NX')) {
            return;
        }

        dispatch(new ProcessBareBonesGameAdded($event->slug))
            ->delay(now()->addMinutes(5));
    }
}
