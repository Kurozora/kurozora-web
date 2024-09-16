<?php

namespace App\Jobs;

use ColorPalette;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kiritokatklian\LaravelColorPalette\Color;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class GenerateImageAttributesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The object containing the media data.
     *
     * @var Media
     */
    protected Media $media;

    /**
     * Create a new job instance.
     *
     * @param Media $media
     *
     * @return void
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // Get file contents for ColorThief, then generate colors.
        $image = file_get_contents($this->media->getFullUrl());
        $colors = $this->generateColorsFor($image);

        // Generate custom properties for the image. Works from URL.
        $dimensions = $this->generateDimensionsFor($this->media->getFullUrl());

        // Add colors and dimensions data to custom properties
        $customProperties = array_merge($colors, $dimensions, $this->media->custom_properties);

        // Update the media with the custom properties.
        $this->media->update([
            'custom_properties' => $customProperties
        ]);
    }

    /**
     * Generate colors for the given image URL/path.
     *
     * @param string $path
     *
     * @return array
     */
    protected function generateColorsFor(string $path): array
    {
        /** @var Color[] $palette */
        $palette = ColorPalette::getPalette($path, 5, 8);

        if (!$palette) {
            return [];
        }

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
     *
     * @return array
     */
    protected function generateDimensionsFor(string $path): array
    {
        [$width, $height] = getimagesize($path);

        return [
            'width' => $width,
            'height' => $height,
        ];
    }
}
