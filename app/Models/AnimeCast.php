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
     * Returns the anime relationship of the cast.
     *
     * @return BelongsTo
     */
    function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Returns the character relationship of the cast.
     *
     * @return BelongsTo
     */
    function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Returns the actor relationship of the cast.
     *
     * @return BelongsTo
     */
    function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }

    /**
     * Returns the role relationship of the cast.
     *
     * @return BelongsTo
     */
    function cast_role(): BelongsTo
    {
        return $this->belongsTo(CastRole::class);
    }

    /**
     * Returns the language relationship of the cast.
     *
     * @return BelongsTo
     */
    function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
