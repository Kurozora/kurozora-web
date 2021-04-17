<?php

namespace App\Traits\User;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasBannerImage
{
    /**
     * Get the user's banner image object.
     *
     * @return Media|null
     */
    function getBannerImageAttribute(): Media|null
    {
        return $this->getFirstMedia($this->bannerImageCollectionName);
    }

    /**
     * Get the URL of the user's banner image.
     *
     * @return string
     */
    function getBannerImageUrlAttribute(): string
    {
        return $this->getFirstMediaFullUrl($this->bannerImageCollectionName);
    }

    /**
     * Updates the user's banner image.
     *
     * @param string|UploadedFile $uploadFile
     *
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    function updateBannerImage(string|UploadedFile $uploadFile)
    {
        $this->addMedia($uploadFile)->toMediaCollection($this->bannerImageCollectionName);
    }

    /**
     * Delete the user's banner image.
     *
     * @return void
     */
    public function deleteBannerImage()
    {
        $this->clearMediaCollection($this->bannerImageCollectionName);
    }
}
