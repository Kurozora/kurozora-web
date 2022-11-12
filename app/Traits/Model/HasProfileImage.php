<?php

namespace App\Traits\Model;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Ramsey\Uuid\Uuid;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

trait HasProfileImage
{
    /**
     * The name of the profile image media collection.
     *
     * @var string $profileImageCollectionName
     */
    protected string $profileImageCollectionName = 'profile';

    /**
     * Get the model's profile image object.
     *
     * @return Media|null
     */
    function getProfileImageAttribute(): Media|null
    {
        return $this->getFirstMedia($this->profileImageCollectionName);
    }

    /**
     * Get the URL of the model's profile image.
     *
     * @return string|null
     */
    function getProfileImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaFullUrl($this->profileImageCollectionName) ?: null;
    }

    /**
     * Updates the model's profile image.
     *
     * @param string|UploadedFile $uploadFile
     * @param string|null $name
     * @param array $customProperties
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    function updateProfileImage(string|UploadedFile $uploadFile, string $name = null, array $customProperties = []): void
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
        $addMedia->toMediaCollection($this->profileImageCollectionName);
    }

    /**
     * Delete the model's profile image.
     *
     * @return void
     */
    public function deleteProfileImage(): void
    {
        $this->clearMediaCollection($this->profileImageCollectionName);
    }
}
