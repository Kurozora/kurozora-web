<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaLibrary;

class Media extends MediaLibrary
{
    // Table name
    const TABLE_NAME = 'media';
    protected $table = self::TABLE_NAME;

    public function temporaryUpload(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public static function findWithTemporaryUploadInCurrentSession(array $uuids) { }
}
