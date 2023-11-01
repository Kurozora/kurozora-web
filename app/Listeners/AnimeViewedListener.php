<?php

namespace App\Listeners;

use App\Events\AnimeViewed;
use App\Models\View;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnimeViewedListener implements ShouldQueue
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
     * @param AnimeViewed $event
     * @return void
     */
    public function handle(AnimeViewed $event): void
    {
        View::create([
            'viewable_id' => $event->anime->id,
            'viewable_type' => $event->anime::class,
        ]);
    }
}
