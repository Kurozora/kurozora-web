<?php

namespace App\Livewire\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\UserStats;
use App\Services\Minigames\Kotodama\StatsService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MyStats extends Component
{
    // Shortest visible bar.
    const int MINIMUM_BAR_PERCENT = 4;

    /**
     * The stats row for the signed-in player.
     *
     * @return UserStats|null
     */
    #[Computed]
    public function stats(): ?UserStats
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        return UserStats::find($user->id)
            ?? StatsService::recompute($user);
    }

    /**
     * The share of games the player has won.
     *
     * @return int
     */
    #[Computed]
    public function winRate(): int
    {
        return (int) round(($this->stats?->getWinRate() ?? 0) * 100);
    }

    /**
     * One bar per guess count.
     *
     * @return Collection
     */
    #[Computed]
    public function distribution(): Collection
    {
        $counts = $this->stats?->guess_distribution ?? [];
        $busiest = max(1, max($counts ?: [0]));

        return collect(range(1, Game::MAX_GUESSES))
            ->map(function (int $bucket) use ($counts, $busiest) {
                $count = (int) ($counts[(string) $bucket] ?? 0);
                $percent = (int) round($count / $busiest * 100);

                return (object) [
                    'bucket' => $bucket,
                    'count' => $count,
                    'percent' => $count > 0 ? max($percent, self::MINIMUM_BAR_PERCENT) : 0,
                ];
            });
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.minigames.kotodama.my-stats', [
            'stats' => $this->stats,
            'winRate' => $this->winRate,
            'distribution' => $this->distribution,
            'averageGuesses' => $this->stats?->getAverageGuesses(),
        ]);
    }
}
