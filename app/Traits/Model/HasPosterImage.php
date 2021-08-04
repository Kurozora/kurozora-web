<?php

namespace App\Traits\Model;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasPosterImage
{
    /**
     * The name of the poster image media collection.
     *
     * @var string $posterImageCollectionName
     */
    protected string $posterImageCollectionName = 'poster';

    /**
     * Get the model's poster image object.
     *
     * @return Media|null
     */
    function getPosterImageAttribute(): Media|null
    {
        return $this->getFirstMedia($this->posterImageCollectionName);
    }

    /**
     * Get the URL of the model's poster image.
     *
     * @return string|null
     */
    function getPosterImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaFullUrl($this->posterImageCollectionName) ?: null;
    }

    /**
     * Updates the model's poster image.
     *
     * @param string|UploadedFile $uploadFile
     * @param string|null $name
     * @param array $customProperties
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    function updatePosterImage(string|UploadedFile $uploadFile, string $name = null, array $customProperties = [])
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
        $addMedia->toMediaCollection($this->posterImageCollectionName);
    }

    /**
     * Delete the model's poster image.
     *
     * @return void
     */
    public function deletePosterImage()
    {
        $this->clearMediaCollection($this->posterImageCollectionName);
    }
}
