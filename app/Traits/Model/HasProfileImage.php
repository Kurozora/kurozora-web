<?php

namespace App\Traits\Model;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
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
     * @param string $name
     * @param array $customProperties
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    function updateProfileImage(string|UploadedFile $uploadFile, string $name, array $customProperties)
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
        $media = $addMedia->toMediaCollection($this->profileImageCollectionName);

        // Add color and dimension data to custom properties
        $colors = $this->generateColorsFor($media->getPath());
        $dimensions = $this->generateDimensionsFor($media->getPath());
        $customProperties = array_merge($colors, $dimensions, $media->custom_properties);
        $media->update([
            'custom_properties' => $customProperties
        ]);
    }

    /**
     * Delete the model's profile image.
     *
     * @return void
     */
    public function deleteProfileImage()
    {
        $this->clearMediaCollection($this->profileImageCollectionName);
    }
}
