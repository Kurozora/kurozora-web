<?php

namespace App\Livewire\Minigames\Kotodama;

use App\Services\Minigames\Kotodama\PuzzleResolver;
use App\Services\Minigames\Kotodama\StatsService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Livewire\Component;

class Leaderboards extends Component
{
    const int LIMIT = 25;

    public string $tab = 'daily';

    /**
     * The query strings of the component.
     *
     * @return array
     */
    protected function queryString(): array
    {
        return [
            'tab' => ['except' => 'daily'],
        ];
    }

    /**
     * Rows for the daily leaderboard tab.
     *
     * @return Collection
     */
    public function getDailyEntriesProperty(): Collection
    {
        try {
            $puzzle = PuzzleResolver::today();
        } catch (ModelNotFoundException) {
            return collect();
        }

        return StatsService::dailyLeaderboard($puzzle, self::LIMIT);
    }

    /**
     * Rows for the streak leaderboard tab.
     *
     * @return Collection
     */
    public function getStreakEntriesProperty(): Collection
    {
        return StatsService::streakLeaderboard(self::LIMIT);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.minigames.kotodama.leaderboards', [
            'dailyEntries' => $this->tab === 'daily' ? $this->dailyEntries : collect(),
            'streakEntries' => $this->tab === 'streak' ? $this->streakEntries : collect(),
        ]);
    }
}
