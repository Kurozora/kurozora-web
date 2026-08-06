<?php

namespace App\Support\Media;

use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\RemoteFile;

class ImageTransformingFileAdder extends FileAdder
{
    /**
     * Adds the pending file to the given collection, transforming eligible images first.
     *
     * @param string $collectionName
     * @param string $diskName
     *
     * @return Media
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function toMediaCollection(string $collectionName = 'default', string $diskName = ''): Media
    {
        if ($this->canTransform()) {
            $converted = (new ImageUploadTransformer)->transform($this->pathToFile, $collectionName);

            if ($converted) {
                $this->fileName = ImageUploadTransformer::webPFileName($this->fileName);
            }

            if (ImageUploadTransformer::isEligibleImage($this->pathToFile)) {
                $this->applyGeneratedImageAttributes();
            }
        }

        return parent::toMediaCollection($collectionName, $diskName);
    }

    /**
     * Merges generated color and dimension custom properties into the pending custom properties.
     *
     * @return void
     */
    protected function applyGeneratedImageAttributes(): void
    {
        $attributeGenerator = new ImageAttributeGenerator;
        $colors = $attributeGenerator->colorsFor($this->pathToFile);
        $dimensions = $attributeGenerator->dimensionsFor($this->pathToFile);

        $this->customProperties = array_merge($colors, $dimensions, $this->customProperties);
    }

    /**
     * Determines whether the pending file is a local file eligible for transformation.
     *
     * @return bool
     */
    protected function canTransform(): bool
    {
        if ($this->file instanceof RemoteFile || $this->isInstanceOfTemporaryUploadModel($this->file)) {
            return false;
        }

        return is_file($this->pathToFile);
    }
}
