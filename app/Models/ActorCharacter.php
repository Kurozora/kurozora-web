<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ActorCharacter extends Pivot
{
    // Table name
    const TABLE_NAME = 'actor_character';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Get the ActorCharacter's actor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actor() {
        return $this->belongsTo(Actor::class);
    }

    /**
     * Get the ActorCharacter's character
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function character() {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the ActorCharacter's anime
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function anime() {
        return $this->belongsToMany(Anime::class, ActorCharacterAnime::TABLE_NAME, 'actor_character_id', 'anime_id');
    }
}
