<?php

namespace App\Traits;

use ColorPalette;
use NikKanetiya\LaravelColorPalette\Color;

trait InteractsWithMediaExtension {
    /**
     * Return the full url of the first media.
     *
     * @param string $collectionName
     * @param string $conversionName
     *
     * @return string
     */
    function getFirstMediaFullUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        $media = $this->getFirstMedia($collectionName);

        if (empty($media)) {
            return $this->getFallbackMediaUrl($collectionName) ?: '';
        }

        if (!empty($conversionName) && !$media->hasGeneratedConversion($conversionName)) {
            return $media->getFullUrl();
        }

        return $media->getFullUrl($conversionName);
    }

    /**
     * Generate colors for the given image URL/path.
     *
     * @param string $path
     * @return array
     */
    function generateColorsFor(string $path): array
    {
        /** @var Color[] $palette */
        $palette = ColorPalette::getPalette($path, 5, 1);

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
    function generateDimensionsFor(string $path): array
    {
        list($width, $height) = getimagesize($path);

        return [
            'width' => $width,
            'height' => $height,
        ];
    }
}
