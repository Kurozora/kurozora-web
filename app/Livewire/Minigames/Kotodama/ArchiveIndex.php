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
        $finishedGames = Game::whereIn('daily_puzzle_id', $puzzles->pluck('id'))
            ->whereIn('mode', [GameMode::Daily, GameMode::Archive])
            ->whereIn('status', [GameStatus::Won, GameStatus::Lost])
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->get(['daily_puzzle_id', 'status']);

        $solvedPuzzleIDs = $finishedGames->filter(fn (Game $game) => $game->status?->is(GameStatus::Won))
            ->pluck('daily_puzzle_id');
        $finishedPuzzleIDs = $finishedGames->pluck('daily_puzzle_id');

        return $puzzles->map(function (DailyPuzzle $puzzle) use ($solvedPuzzleIDs, $finishedPuzzleIDs) {
            return (object) [
                'date' => $puzzle->puzzle_date?->toDateString(),
                'formattedDate' => $puzzle->puzzle_date?->locale(app()->getLocale())->isoFormat('ll'),
                'puzzleNumber' => $puzzle->puzzle_number,
                'solved' => $solvedPuzzleIDs->contains($puzzle->id),
                'finished' => $finishedPuzzleIDs->contains($puzzle->id),
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
