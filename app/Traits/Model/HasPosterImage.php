<?php

namespace App\Traits\Model;

use Illuminate\Http\UploadedFile;
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
     * @param string|null $extension
     * @throws FileCannotBeAdded
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    function updatePosterImage(string|UploadedFile $uploadFile, string $name = null, array $customProperties = [], ?string $extension = null): void
    {
        // Determine media adder
        if ($isUrl = str($uploadFile)->startsWith(['http://', 'https://'])) {
            $addMedia = $this->addMediaFromUrl($uploadFile);
        } elseif ($isUploadFile = $uploadFile instanceof UploadedFile) {
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
        } elseif (!empty($isUploadFile)) {
            $addMedia->usingFileName(Uuid::uuid4() . '.' . $uploadFile->extension());
        } else {
            $extension = $extension ?? 'jpg';
            $addMedia->usingFileName(Uuid::uuid4() . '.' . $extension);
        }

        // Add media
        $addMedia->toMediaCollection($this->posterImageCollectionName);
    }

    /**
     * Delete the model's poster image.
     *
     * @return void
     */
    public function deletePosterImage(): void
    {
        $this->clearMediaCollection($this->posterImageCollectionName);
    }
}
