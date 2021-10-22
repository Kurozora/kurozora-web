<?php

namespace App\Listeners;

use ColorPalette;
use NikKanetiya\LaravelColorPalette\Color;
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
    public function handle(MediaHasBeenAdded $event)
    {
        // Add color and dimension data to custom properties
        $colors = $this->generateColorsFor($event->media->getPath());
        $dimensions = $this->generateDimensionsFor($event->media->getPath());
        $customProperties = array_merge($colors, $dimensions, $event->media->custom_properties);
        $event->media->update([
            'custom_properties' => $customProperties
        ]);
    }

    /**
     * Generate colors for the given image URL/path.
     *
     * @param string $path
     * @return array
     */
    protected function generateColorsFor(string $path): array
    {
        /** @var Color[] $palette */
        $palette = ColorPalette::getPalette($path, 5, 8);

        return [
            'background_color' => $palette[0]->toHexString(),
            'text_color_1' => $palette[1]->toHexString(),
            'text_color_2' => $palette[2]->toHexString(),
            'text_color_3' => $palette[3]->toHexString(),
            'text_color_4' => $palette[4]->toHexString(),
        ];
    }

    /**
     * Generate dimensions for the given image URL/path.
     *
     * @param string $path
     * @return array
     */
    protected function generateDimensionsFor(string $path): array
    {
        list($width, $height) = getimagesize($path);

        return [
            'width' => $width,
            'height' => $height,
        ];
    }
}
