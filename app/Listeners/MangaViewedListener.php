<?php

namespace App\Listeners;

use App\Events\MangaViewed;
use App\Models\View;
use Illuminate\Contracts\Queue\ShouldQueue;

class MangaViewedListener implements ShouldQueue
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
     * @param MangaViewed $event
     * @return void
     */
    public function handle(MangaViewed $event): void
    {
        View::create([
            'viewable_id' => $event->manga->id,
            'viewable_type' => $event->manga::class,
        ]);
    }
}
