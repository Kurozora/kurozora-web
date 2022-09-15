<?php

namespace App\Listeners;

use App\Events\CharacterViewed;
use App\Models\View;

class CharacterViewedListener
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
     * @param CharacterViewed $event
     * @return void
     */
    public function handle(CharacterViewed $event): void
    {
        View::create([
            'viewable_id' => $event->character->id,
            'viewable_type' => $event->character::class,
        ]);
    }
}
