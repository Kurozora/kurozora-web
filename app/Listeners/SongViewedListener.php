<?php

namespace App\Listeners;

use App\Events\SongViewed;
use App\Models\View;
use Illuminate\Contracts\Queue\ShouldQueue;

class SongViewedListener implements ShouldQueue
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
     * @param SongViewed $event
     * @return void
     */
    public function handle(SongViewed $event): void
    {
        View::create([
            'viewable_id' => $event->song->id,
            'viewable_type' => $event->song::class,
        ]);
    }
}
