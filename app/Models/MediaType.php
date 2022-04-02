<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaType extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'media_types';
    protected $table = self::TABLE_NAME;

    /**
     * The anime that the media type has.
     *
     * @return HasMany
     */
    public function anime(): HasMany
    {
        return $this->hasMany(Anime::class);
    }
}
