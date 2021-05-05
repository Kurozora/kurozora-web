<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AnimeCast extends Pivot
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'anime_casts';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the anime relationship of the object.
     *
     * @return BelongsTo
     */
    function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Returns the character relationship of the object.
     *
     * @return BelongsTo
     */
    function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Returns the actor relationship of the object.
     *
     * @return BelongsTo
     */
    function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }
}
