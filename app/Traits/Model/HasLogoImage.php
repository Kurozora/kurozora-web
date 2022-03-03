<?php

namespace App\Traits\Model;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Ramsey\Uuid\Uuid;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

trait HasLogoImage
{
    /**
     * The name of the logo image media collection.
     *
     * @var string $logoImageCollectionName
     */
    protected string $logoImageCollectionName = 'logo';

    /**
     * Get the model's logo image object.
     *
     * @return Media|null
     */
    function getLogoImageAttribute(): Media|null
    {
        return $this->getFirstMedia($this->logoImageCollectionName);
    }

    /**
     * Get the URL of the model's logo image.
     *
     * @return string|null
     */
    function getLogoImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaFullUrl($this->logoImageCollectionName) ?: null;
    }

    /**
     * Updates the model's logo image.
     *
     * @param string|UploadedFile $uploadFile
     * @param string|null $name
     * @param array $customProperties
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    function updateLogoImage(string|UploadedFile $uploadFile, string $name = null, array $customProperties = [])
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
        $addMedia->toMediaCollection($this->logoImageCollectionName);
    }

    /**
     * Delete the model's logo image.
     *
     * @return void
     */
    public function deleteLogoImage()
    {
        $this->clearMediaCollection($this->logoImageCollectionName);
    }
}
