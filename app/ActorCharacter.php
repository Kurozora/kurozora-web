<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ActorCharacter extends Pivot
{
    // Table name
    const TABLE_NAME = 'actor_character';
    protected $table = self::TABLE_NAME;
}
