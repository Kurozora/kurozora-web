<?php

namespace App\Traits\Model;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
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
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    function updateBannerImage(string|UploadedFile $uploadFile, string $name = null, array $customProperties = [])
    {
        // Determine media adder
        if (Str::startsWith($uploadFile, ['http://', 'https://'])) {
            $addMedia = $this->addMediaFromUrl($uploadFile);
        } else {
            $addMedia = $this->addMedia($uploadFile);
        }

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
        $media = $addMedia->toMediaCollection($this->bannerImageCollectionName);

        // Add color and dimension data to custom properties
        $colors = $this->generateColorsFor($media->getPath());
        $dimensions = $this->generateDimensionsFor($media->getPath());
        $customProperties = array_merge($colors, $dimensions, $media->custom_properties);
        $media->update([
            'custom_properties' => $customProperties
        ]);
    }

    /**
     * Delete the model's banner image.
     *
     * @return void
     */
    public function deleteBannerImage()
    {
        $this->clearMediaCollection($this->bannerImageCollectionName);
    }
}
