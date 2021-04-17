<?php

namespace App\Traits\User;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasProfileImage
{
    /**
     * Get the user's profile image object.
     *
     * @return Media|null
     */
    function getProfileImageAttribute(): Media|null
    {
        return $this->getFirstMedia($this->profileImageCollectionName);
    }

    /**
     * Get the URL of the user's profile image.
     *
     * @return string
     */
    function getProfileImageUrlAttribute(): string
    {
        return $this->getFirstMediaFullUrl($this->profileImageCollectionName);
    }

    /**
     * Updates the user's profile image.
     *
     * @param string|UploadedFile $uploadFile
     *
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    function updateProfileImage(string|UploadedFile $uploadFile)
    {
        $this->addMedia($uploadFile)->toMediaCollection($this->profileImageCollectionName);
    }

    /**
     * Delete the user's profile image.
     *
     * @return void
     */
    public function deleteProfileImage()
    {
        $this->clearMediaCollection($this->profileImageCollectionName);
    }
}
