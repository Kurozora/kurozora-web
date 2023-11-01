<?php

namespace App\Listeners;

use App\Events\StudioViewed;
use App\Models\View;
use Illuminate\Contracts\Queue\ShouldQueue;

class StudioViewedListener implements ShouldQueue
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
     * @param StudioViewed $event
     * @return void
     */
    public function handle(StudioViewed $event): void
    {
        View::create([
            'viewable_id' => $event->studio->id,
            'viewable_type' => $event->studio::class,
        ]);
    }
}
