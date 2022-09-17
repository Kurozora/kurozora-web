<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends KModel
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const TABLE_NAME = 'status';
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
