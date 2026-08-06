<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\UserStats;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserStatsResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var UserStats $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->resource->user_id,
            'type' => 'kotodama-user-stats',
            'href' => route('api.kotodama.me.stats', [], false),
            'attributes' => [
                'currentStreak' => (int) $this->resource->current_streak,
                'maxStreak' => (int) $this->resource->max_streak,
                'gamesPlayed' => (int) $this->resource->games_played,
                'gamesWon' => (int) $this->resource->games_won,
                'winRate' => $this->resource->getWinRate(),
                'guessDistribution' => (object) ($this->resource->guess_distribution ?? []),
                'averageGuesses' => $this->resource->getAverageGuesses(),
                'averageDurationMs' => $this->resource->getAverageDurationMs(),
                'lastDailyDate' => $this->resource->last_daily_date?->toDateString(),
            ],
        ];
    }
}
