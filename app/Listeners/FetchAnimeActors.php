<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;

class FetchAnimeActors implements ShouldQueue
{
    /**
     * Only queue the listener if the actors have not been fetched
     *
     * @param $event
     * @return bool
     */
    public function shouldQueue($event)
    {
        return !$event->anime->fetched_actors && $event->anime->tvdb_id !== null;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Artisan::call('animes:fetch_actors', ['id' => $event->anime->id]);
    }
}
