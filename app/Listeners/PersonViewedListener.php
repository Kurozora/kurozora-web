<?php

namespace App\Listeners;

use App\Events\PersonViewed;
use App\Models\View;

class PersonViewedListener
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
     * @param PersonViewed $event
     * @return void
     */
    public function handle(PersonViewed $event): void
    {
        View::create([
            'viewable_id' => $event->person->id,
            'viewable_type' => $event->person::class,
        ]);
    }
}
