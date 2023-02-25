<?php

namespace App\Listeners;

use App\Events\GameViewed;
use App\Models\View;

class GameViewedListener
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
     * @param GameViewed $event
     * @return void
     */
    public function handle(GameViewed $event): void
    {
        View::create([
            'viewable_id' => $event->game->id,
            'viewable_type' => $event->game::class,
        ]);
    }
}
