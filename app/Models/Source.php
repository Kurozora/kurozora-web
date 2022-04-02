<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'sources';
    protected $table = self::TABLE_NAME;

    /**
     * The anime that the source has.
     *
     * @return HasMany
     */
    public function anime(): HasMany
    {
        return $this->hasMany(Anime::class);
    }
}
