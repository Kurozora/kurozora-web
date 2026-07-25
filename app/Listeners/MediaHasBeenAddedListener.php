<?php

namespace App\Listeners;

use App\Jobs\GenerateImageAttributesJob;
use Bus;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class MediaHasBeenAddedListener
{
    /**
     * List of images that are allowed to be converted to WEBP.
     *
     * @var array|string[] $imageMimeTypes
     */
    protected array $imageMimeTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

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
     * Fallback for media whose attributes weren't generated upfront during upload, e.g. remote-disk adds.
     *
     * @param  MediaHasBeenAddedEvent  $event
     * @return void
     */
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if (in_array($media->mime_type, $this->imageMimeTypes) && !$media->hasCustomProperty('width')) {
            Bus::chain([
                new GenerateImageAttributesJob($media),
            ])->dispatch();
        }
    }
}
