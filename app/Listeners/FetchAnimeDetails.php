<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;

class FetchAnimeDetails implements ShouldQueue
{
    /**
     * Only queue the listener if the details have not been fetched
     *
     * @param $event
     * @return bool
     */
    public function shouldQueue($event)
    {
        return !$event->anime->fetched_details;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Artisan::call('animes:fetch_details', ['id' => $event->anime->id]);
    }
}
