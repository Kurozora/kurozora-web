<?php

namespace App\Traits\Model;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

trait HasScreenshotImage
{
    /**
     * The name of the screenshot image media collection.
     *
     * @var string $screenshotImageCollectionName
     */
    protected string $screenshotImageCollectionName = 'screenshot';

    /**
     * Get the model's screenshot image object.
     *
     * @return Media|null
     */
    function getScreenshotImageAttribute(): Media|null
    {
        return $this->getFirstMedia($this->screenshotImageCollectionName);
    }

    /**
     * Get the model's screenshot image collection object.
     *
     * @return MediaCollection|null
     */
    function getScreenshotImageCollectionAttribute(): MediaCollection|null
    {
        return $this->getMedia($this->screenshotImageCollectionName);
    }

    /**
     * Get the URL of the model's screenshot image.
     *
     * @return string|null
     */
    function getScreenshotImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaFullUrl($this->screenshotImageCollectionName) ?: null;
    }

    /**
     * Updates the model's screenshot image.
     *
     * @param string|UploadedFile $uploadFile
     * @param string|null $name
     * @param array $customProperties
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    function updateScreenshotImage(string|UploadedFile $uploadFile, string $name = null, array $customProperties = [])
    {
        // Determine media adder
        $addMedia = Str::startsWith($uploadFile, ['http://', 'https://']) ? $this->addMediaFromUrl($uploadFile) : $this->addMedia($uploadFile);

        // Configure properties
        if (!empty($name)) {
            $addMedia->usingName($name);
        }
        if (!empty($customProperties)) {
            $addMedia->withCustomProperties($customProperties);
        }
        if (is_string($uploadFile)) {
            $extension = pathinfo($uploadFile, PATHINFO_EXTENSION);
            $addMedia->usingFileName(Uuid::uuid4() . '.' . $extension);
        } else {
            $addMedia->usingFileName(Uuid::uuid4() . '.' . $uploadFile->extension());
        }

        // Add media
        $addMedia->toMediaCollection($this->screenshotImageCollectionName);
    }

    /**
     * Delete the model's screenshot image.
     *
     * @return void
     */
    public function deleteScreenshotImage()
    {
        $this->clearMediaCollection($this->screenshotImageCollectionName);
    }
}
