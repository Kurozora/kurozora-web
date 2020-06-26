<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ActorAnimeCharacter extends Pivot
{
    // Table name
    const TABLE_NAME = 'actor_anime_character';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
