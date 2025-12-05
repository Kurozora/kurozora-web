<?php

namespace App\Traits;

use App\Enums\MediaCollection;
use Illuminate\Http\UploadedFile;
use Ramsey\Uuid\Uuid;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

trait InteractsWithMediaExtension {
    /**
     * Return the full url of the first media for the specified media collection.
     *
     * @param MediaCollection|null $collectionName
     * @param string $conversionName
     *
     * @return string|null
     */
    function getFirstMediaFullUrl(?MediaCollection $collectionName = null, string $conversionName = ''): ?string
    {
        $collectionName = $collectionName ?? MediaCollection::Default();

        if ($this->relationLoaded('media')) {
            $media = $this->media->where('collection_name', '=', $collectionName->value)->first();
        } else {
            $media = $this->getFirstMedia($collectionName->value);
        }

        if (empty($media)) {
            return $this->getFallbackMediaUrl($collectionName->value) ?: null;
        }

        if (!empty($conversionName) && !$media->hasGeneratedConversion($conversionName)) {
            return $media->getFullUrl() ?: null;
        }

        return $media->getFullUrl($conversionName) ?: null;
    }

    /**
     * Updates the image media of the model for the specified media collection.
     *
     * @param MediaCollection $mediaCollection
     * @param string|UploadedFile $uploadFile
     * @param string|null $name
     * @param array $customProperties
     * @param string|null $extension
     * @throws FileCannotBeAdded
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    function updateImageMedia(MediaCollection $mediaCollection, string|UploadedFile $uploadFile, ?string $name = null, array $customProperties = [], ?string $extension = null): void
    {
        if (empty($uploadFile)) {
            return;
        }

        // Determine media adder
        if ($isUrl = str($uploadFile)->startsWith(['http://', 'https://'])) {
            $addMedia = $this->addMediaFromUrl($uploadFile);
        } else if ($isUploadFile = $uploadFile instanceof UploadedFile) {
            $addMedia = $this->addMedia($uploadFile);
        } else if ($isPath = @file_exists($uploadFile)) {
            $addMedia = $this->addMedia($uploadFile);
        } else {
            $addMedia = $this->addMediaFromStream($uploadFile);
        }

        // Configure properties
        if (!empty($name)) {
            $addMedia->usingName($name);
        }
        if (!empty($customProperties)) {
            $addMedia->withCustomProperties($customProperties);
        }

        if ($isUrl) {
            $extension = $extension ?? pathinfo($uploadFile, PATHINFO_EXTENSION);
            $addMedia->usingFileName(Uuid::uuid4() . '.' . $extension);
        } else if (!empty($isUploadFile)) {
            $addMedia->usingFileName(Uuid::uuid4() . '.' . $uploadFile->extension());
        } else if (!empty($isPath)) {
            $extension = $extension ?? pathinfo($uploadFile, PATHINFO_EXTENSION);
            $addMedia->usingFileName(Uuid::uuid4() . '.' . $extension);
        } else {
            $extension = $extension ?? 'jpg';
            $addMedia->usingFileName(Uuid::uuid4() . '.' . $extension);
        }

        // Add media
        $addMedia->toMediaCollection($mediaCollection->value);
    }
}
