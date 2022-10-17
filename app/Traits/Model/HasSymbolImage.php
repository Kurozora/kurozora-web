<?php

namespace App\Traits\Model;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Ramsey\Uuid\Uuid;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

trait HasSymbolImage
{
    /**
     * The name of the symbol image media collection.
     *
     * @var string $symbolImageCollectionName
     */
    protected string $symbolImageCollectionName = 'symbol';

    /**
     * Get the model's symbol image object.
     *
     * @return Media|null
     */
    function getSymbolImageAttribute(): Media|null
    {
        return $this->getFirstMedia($this->symbolImageCollectionName);
    }

    /**
     * Get the URL of the model's symbol image.
     *
     * @return string|null
     */
    function getSymbolImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaFullUrl($this->symbolImageCollectionName) ?: null;
    }

    /**
     * Updates the model's symbol image.
     *
     * @param string|UploadedFile $uploadFile
     * @param string|null $name
     * @param array $customProperties
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    function updateSymbolImage(string|UploadedFile $uploadFile, string $name = null, array $customProperties = []): void
    {
        // Determine media adder
        $addMedia = str($uploadFile)->startsWith(['http://', 'https://']) ? $this->addMediaFromUrl($uploadFile) : $this->addMedia($uploadFile);

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
        $addMedia->toMediaCollection($this->symbolImageCollectionName);
    }

    /**
     * Delete the model's symbol image.
     *
     * @return void
     */
    public function deleteSymbolImage(): void
    {
        $this->clearMediaCollection($this->symbolImageCollectionName);
    }
}
