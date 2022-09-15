<?php

namespace App\Listeners;

use App\Events\EpisodeViewed;
use App\Models\View;

class EpisodeViewedListener
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
     * @param EpisodeViewed $event
     * @return void
     */
    public function handle(EpisodeViewed $event): void
    {
        View::create([
            'viewable_id' => $event->episode->id,
            'viewable_type' => $event->episode::class,
        ]);
    }
}
