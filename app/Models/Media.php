<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaLibrary;

class Media extends MediaLibrary
{
    // Table name
    const string TABLE_NAME = 'media';
    protected $table = self::TABLE_NAME;

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Media $model) {
            if ($model->hasCustomProperty('width')) {
                $width = (int) $model->custom_properties['width'];
                $model->setCustomProperty('width', $width);
            }

            if ($model->hasCustomProperty('height')) {
                $height = (int) $model->custom_properties['height'];
                $model->setCustomProperty('height', $height);
            }
        });
    }

    public function temporaryUpload(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Returns the CSS `object-position` value to use when this media is rendered with `object-fit: cover` inside a 1:1 (square) container such as a circular avatar.
     *
     * Mirrors the iOS `CircularImageView.applyFocalPointCrop()` math: the visible window is anchored on the focal point but clamped so the crop never exposes transparent gutters at the image's edges. CSS `object-position` percentages are interpreted against the image's overflow, not against the source image directly, so a translation step is required for the two platforms to crop identically. Falls back to `center` when focal data or dimensions are missing.
     *
     * @return string
     */
    public function objectPositionStyle(): string
    {
        $focalX = $this->getCustomProperty('focal_x');
        $focalY = $this->getCustomProperty('focal_y');
        $width = $this->getCustomProperty('width');
        $height = $this->getCustomProperty('height');

        if ($focalX === null || $focalY === null || !$width || !$height) {
            return 'center';
        }

        $imageAspect = $width / $height;

        if ($imageAspect < 1) {
            $halfWindow = $imageAspect / 2;
            $clampedY = max($halfWindow, min(1 - $halfWindow, $focalY));
            $yPercent = ($clampedY - $halfWindow) / (1 - $imageAspect) * 100;

            return sprintf('50%% %.4f%%', $yPercent);
        }

        if ($imageAspect > 1) {
            $halfWindow = 1 / (2 * $imageAspect);
            $clampedX = max($halfWindow, min(1 - $halfWindow, $focalX));
            $xPercent = ($clampedX - $halfWindow) / (1 - 1 / $imageAspect) * 100;

            return sprintf('%.4f%% 50%%', $xPercent);
        }

        return 'center';
    }
}
