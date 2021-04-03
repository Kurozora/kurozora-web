<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;

class FetchBaseAnimeEpisodes implements ShouldQueue
{
    /**
     * Only queue the listener if the base episodes have not been fetched
     *
     * @param $event
     * @return bool
     */
    public function shouldQueue($event)
    {
        return !$event->anime->fetched_base_episodes && $event->anime->tvdb_id !== null;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Artisan::call('animes:fetch_base_episodes', ['id' => $event->anime->id]);
    }
}
