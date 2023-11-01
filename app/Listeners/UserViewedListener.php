<?php

namespace App\Listeners;

use App\Events\UserViewed;
use App\Models\View;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserViewedListener implements ShouldQueue
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
