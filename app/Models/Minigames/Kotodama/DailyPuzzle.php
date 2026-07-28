<?php

namespace App\Models\Minigames\Kotodama;

use App\Models\KModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyPuzzle extends KModel
{
    // Table name
    const string TABLE_NAME = 'kotodama_daily_puzzles';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'puzzle_date'   => 'date',
            'puzzle_number' => 'int',
        ];
    }

    /**
     * Returns the word relationship.
     *
     * @return BelongsTo
     */
    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'word_id');
    }

    /**
     * Returns the games played against this puzzle.
     *
     * @return HasMany
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'daily_puzzle_id');
    }
}
