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
}
