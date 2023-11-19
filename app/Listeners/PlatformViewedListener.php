<?php

namespace App\Listeners;

use App\Events\PlatformViewed;
use App\Models\View;
use Illuminate\Contracts\Queue\ShouldQueue;

class PlatformViewedListener implements ShouldQueue
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
     *
     * @param PlatformViewed $event
     * @return void
     */
    public function handle(PlatformViewed $event): void
    {
        View::create([
            'viewable_id' => $event->platform->id,
            'viewable_type' => $event->platform::class,
        ]);
    }
}
