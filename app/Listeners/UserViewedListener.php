<?php

namespace App\Listeners;

use App\Events\UserViewed;
use App\Models\View;

class UserViewedListener
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
     * @param UserViewed $event
     * @return void
     */
    public function handle(UserViewed $event): void
    {
        View::create([
            'viewable_id' => $event->user->id,
            'viewable_type' => $event->user::class,
        ]);
    }
}
