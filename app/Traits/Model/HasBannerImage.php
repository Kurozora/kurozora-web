<?php

namespace App\Traits\Model;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Ramsey\Uuid\Uuid;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

trait HasBannerImage
{
    /**
     * The name of the banner image media collection.
     *
     * @var string $bannerImageCollectionName
     */
    protected string $bannerImageCollectionName = 'banner';

    /**
     * Get the model's banner image object.
     *
     * @return Media|null
     */
    function getBannerImageAttribute(): Media|null
    {
        return $this->getFirstMedia($this->bannerImageCollectionName);
    }

    /**
     * Get the URL of the model's banner image.
     *
     * @return string|null
     */
    function getBannerImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaFullUrl($this->bannerImageCollectionName) ?: null;
    }

    /**
     * Updates the model's banner image.
     *
     * @param string|UploadedFile $uploadFile
     * @param string|null $name
     * @param array $customProperties
     * @param string|null $extension
     * @throws FileCannotBeAdded
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    function updateBannerImage(string|UploadedFile $uploadFile, string $name = null, array $customProperties = [], ?string $extension = null): void
    {
        // Determine media adder
        if ($isUrl = str($uploadFile)->startsWith(['http://', 'https://'])) {
            $addMedia = $this->addMediaFromUrl($uploadFile);
        } elseif ($isUploadFile = $uploadFile instanceof UploadedFile) {
            $addMedia = $this->addMedia($uploadFile);
        } elseif ($isPath = @file_exists($uploadFile)) {
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
        } elseif (!empty($isPath)) {
            $extension = $extension ?? pathinfo($uploadFile, PATHINFO_EXTENSION);
            $addMedia->usingFileName(Uuid::uuid4() . '.' . $extension);
        } else {
            $extension = $extension ?? 'jpg';
            $addMedia->usingFileName(Uuid::uuid4() . '.' . $extension);
        }

        // Add media
        $addMedia->toMediaCollection($this->bannerImageCollectionName);
    }

    /**
     * Delete the model's banner image.
     *
     * @return void
     */
    public function deleteBannerImage(): void
    {
        $this->clearMediaCollection($this->bannerImageCollectionName);
    }
}
