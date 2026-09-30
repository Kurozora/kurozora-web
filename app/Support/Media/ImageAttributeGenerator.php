<?php

namespace App\Support\Media;

use ColorPalette;
use Kiritokatklian\LaravelColorPalette\Color;

class ImageAttributeGenerator
{
    /**
     * Builds the background and text color custom properties from the given image.
     *
     * @param string $filePath
     * @return array
     */
    public function colorsFor(string $filePath): array
    {
        /** @var Color[] $palette */
        $palette = ColorPalette::getPalette($filePath, 5, 8);

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
     * Builds the width/height custom properties from the given image.
     *
     * @param string $filePath
     * @return array
     */
    public function dimensionsFor(string $filePath): array
    {
        $dimensions = getimagesize($filePath);

        if ($dimensions === false) {
            return [];
        }

        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
        ];
    }
}
