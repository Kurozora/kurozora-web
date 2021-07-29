<?php

namespace App\Traits;

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
}
