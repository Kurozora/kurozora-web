<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ActorCharacterAnime extends Pivot
{
    // Table name
    const TABLE_NAME = 'actor_character_anime';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    function anime() {
        return $this->belongsTo(Anime::class);
    }

    function actor_character() {
        return $this->belongsTo(ActorCharacter::class);
    }
}
