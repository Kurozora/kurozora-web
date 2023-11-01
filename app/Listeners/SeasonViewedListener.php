<?php

namespace App\Listeners;

use App\Events\SeasonViewed;
use App\Models\View;
use Illuminate\Contracts\Queue\ShouldQueue;

class SeasonViewedListener implements ShouldQueue
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
     * @param SeasonViewed $event
     * @return void
     */
    public function handle(SeasonViewed $event): void
    {
        View::create([
            'viewable_id' => $event->season->id,
            'viewable_type' => $event->season::class,
        ]);
    }
}
