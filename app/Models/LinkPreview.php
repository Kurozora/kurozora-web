<?php

namespace App\Models;

use App\Enums\LinkPreviewType;

class LinkPreview extends KModel
{
    // Table name
    const string TABLE_NAME = 'link_previews';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'type' => LinkPreviewType::class,
            'fetched_at' => 'datetime',
        ];
    }
}
