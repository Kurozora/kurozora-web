<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ActorCharacter extends Pivot
{
    use HasFactory;

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
     * @return BelongsTo
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }

    /**
     * Get the ActorCharacter's character
     *
     * @return BelongsTo
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the ActorCharacter's anime
     *
     * @return BelongsToMany
     */
    public function anime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, ActorCharacterAnime::TABLE_NAME, 'actor_character_id', 'anime_id');
    }
}
