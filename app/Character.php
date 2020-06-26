<?php

namespace App;

class Character extends KModel
{
    // Table name
    const TABLE_NAME = 'characters';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the anime the character belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function anime() {
        return $this->belongsToMany(Anime::class, ActorAnimeCharacter::TABLE_NAME);
    }

    /**
     * Returns the actors the character belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function actors() {
        return $this->belongsToMany(Actor::class, ActorAnimeCharacter::TABLE_NAME);
    }
}
