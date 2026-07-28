<?php

namespace App\Models\Minigames\Kotodama;

use App\Models\KModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStats extends KModel
{
    // Table name
    const string TABLE_NAME = 'kotodama_user_stats';
    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'current_streak'     => 'int',
            'max_streak'         => 'int',
            'games_played'       => 'int',
            'games_won'          => 'int',
            'total_duration_ms'  => 'int',
            'guess_distribution' => 'array',
            'last_daily_date'    => 'date',
        ];
    }

    /**
     * Returns the user these stats belong to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the win rate.
     *
     * @return float
     */
    public function getWinRate(): float
    {
        if ($this->games_played <= 0) {
            return 0.0;
        }

        return round($this->games_won / $this->games_played, 4);
    }

    /**
     * Returns the average number of guesses across winning games.
     *
     * @return float
     */
    public function getAverageGuesses(): float
    {
        $distribution = $this->guess_distribution ?? [];
        $sum = 0;
        $count = 0;

        foreach ($distribution as $bucket => $occurrences) {
            $sum += (int) $bucket * (int) $occurrences;
            $count += (int) $occurrences;
        }

        if ($count <= 0) {
            return 0.0;
        }

        return round($sum / $count, 2);
    }

    /**
     * Returns the average duration across finished games.
     *
     * @return int
     */
    public function getAverageDurationMs(): int
    {
        if ($this->games_played <= 0) {
            return 0;
        }

        return (int) round($this->total_duration_ms / $this->games_played);
    }
}
