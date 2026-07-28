<?php

namespace App\Models\Minigames\Kotodama;

use App\Models\KModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guess extends KModel
{
    // Table name
    const string TABLE_NAME = 'kotodama_guesses';
    protected $table = self::TABLE_NAME;

    // Append-only.
    const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'position'   => 'int',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Returns the game this guess belongs to.
     *
     * @return BelongsTo
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
