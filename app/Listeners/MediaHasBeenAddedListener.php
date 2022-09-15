<?php

namespace App\Listeners;

use App\Jobs\ConvertImageToWebPJob;
use App\Jobs\GenerateImageAttributesJob;
use Bus;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;

class MediaHasBeenAddedListener
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
     * @param  MediaHasBeenAdded  $event
     * @return void
     */
    public function handle(MediaHasBeenAdded $event): void
    {
        Bus::chain([
            new ConvertImageToWebPJob($event->media),
            new GenerateImageAttributesJob($event->media),
        ])->dispatch();
    }
}
