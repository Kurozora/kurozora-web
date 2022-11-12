<?php

namespace App\Listeners;

use App\Jobs\ConvertImageToWebPJob;
use App\Jobs\GenerateImageAttributesJob;
use Bus;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;

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
     * @param  MediaHasBeenAdded  $event
     * @return void
     */
    public function handle(MediaHasBeenAdded $event): void
    {
        $media = $event->media;

        if (in_array($media->mime_type, $this->imageMimeTypes)) {
            Bus::chain([
                new GenerateImageAttributesJob($media),
                new ConvertImageToWebPJob($media),
            ])->dispatch();
        }
    }
}
