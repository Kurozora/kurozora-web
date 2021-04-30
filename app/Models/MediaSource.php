<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaSource extends Model
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'media_sources';
    protected $table = self::TABLE_NAME;

    /**
     * The anime that the media source has.
     *
     * @return HasMany
     */
    public function anime(): HasMany
    {
        return $this->hasMany(Anime::class);
    }
}
