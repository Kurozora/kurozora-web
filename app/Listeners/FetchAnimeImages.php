<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;

class FetchAnimeImages implements ShouldQueue
{
    /**
     * Only queue the listener if the images have not been fetched
     *
     * @param $event
     * @return bool
     */
    public function shouldQueue($event)
    {
        return !$event->anime->fetched_images && $event->anime->tvdb_id !== null;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Artisan::call('animes:fetch_images', ['id' => $event->anime->id]);
    }
}
