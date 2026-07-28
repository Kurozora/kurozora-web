<?php

namespace App\Livewire\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class ArchiveIndex extends Component
{
    const int LIMIT = 365;

    /**
     * The past puzzles available to play.
     *
     * @return Collection
     */
    public function getEntriesProperty(): Collection
    {
        $puzzles = DailyPuzzle::where('puzzle_date', '<', Carbon::now()->toDateString())
            ->orderByDesc('puzzle_date')
            ->limit(self::LIMIT)
            ->get();

        $user = auth()->user();
        $solvedPuzzleIDs = Game::whereIn('daily_puzzle_id', $puzzles->pluck('id'))
            ->where('mode', GameMode::Archive)
            ->where('status', GameStatus::Won)
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->pluck('daily_puzzle_id');

        return $puzzles->map(function (DailyPuzzle $puzzle) use ($solvedPuzzleIDs) {
            return (object) [
                'date' => $puzzle->puzzle_date?->toDateString(),
                'puzzleNumber' => $puzzle->puzzle_number,
                'solved' => $solvedPuzzleIDs->contains($puzzle->id),
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
        return view('livewire.minigames.kotodama.archive-index', [
            'entries' => $this->entries,
        ]);
    }
}
