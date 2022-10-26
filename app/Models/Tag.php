<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends KModel
{
    use HasUlids,
        SoftDeletes;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool $incrementing
     */
    public $incrementing = false;

    // Table name
    const TABLE_NAME = 'tags';
    protected $table = self::TABLE_NAME;

    /**
     * The media tag the tag belongs to.
     *
     * @return HasMany
     */
    public function mediaTags(): HasMany
    {
        return $this->hasMany(MediaTag::class);
    }
}
