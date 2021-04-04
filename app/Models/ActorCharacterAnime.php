<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ActorCharacterAnime extends Pivot
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'actor_character_anime';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

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
     * Returns the actor_character relationship of the object.
     *
     * @return BelongsTo
     */
    function actor_character(): BelongsTo
    {
        return $this->belongsTo(ActorCharacter::class);
    }
}
