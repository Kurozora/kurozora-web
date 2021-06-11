<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'songs';
    protected $table = self::TABLE_NAME;

    /**
     * Get the anime-songs relationship.
     *
     * @return HasMany
     */
    public function anime_songs(): HasMany
    {
        return $this->hasMany(AnimeSong::class);
    }
}
