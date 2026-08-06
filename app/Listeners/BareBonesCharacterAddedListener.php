<?php

namespace App\Listeners;

use App\Events\BareBonesCharacterAdded;
use App\Jobs\ProcessBareBonesCharacterAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;

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
        if (!Redis::set('mal:bb:character:' . $event->malID, 1, 'EX', 86400, 'NX')) {
            return;
        }

        dispatch(new ProcessBareBonesCharacterAdded($event->malID))
            ->delay(now()->addMinutes(5));
    }
}
