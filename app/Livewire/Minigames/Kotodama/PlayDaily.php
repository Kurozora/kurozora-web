<?php

namespace App\Livewire\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\UserStats;
use App\Models\Minigames\Kotodama\Word;
use App\Services\Minigames\Kotodama\GameCoordinator;
use App\Services\Minigames\Kotodama\PuzzleResolver;
use App\Services\Minigames\Kotodama\ShareGridFormatter;
use App\Services\Minigames\Kotodama\StatsService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PlayDaily extends Component
{
    const int RECENT_DAYS = 7;
    const int PEEK_LIMIT = 3;

    /**
     * The current game ID.
     *
     * @var int|null
     */
    public ?int $gameId = null;

    /**
     * The flash message.
     *
     * @var string|null
     */
    public ?string $flash = null;

    /**
     * The resolved puzzle.
     *
     * @var DailyPuzzle|null
     */
    public ?DailyPuzzle $puzzle = null;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        try {
            $this->puzzle = PuzzleResolver::today();
        } catch (ModelNotFoundException) {
            $this->flash = __('No puzzle is available today.');
            return;
        }

        $game = GameCoordinator::startDaily(
            $this->puzzle,
            auth()->user(),
            GameCoordinator::guestTokenFor(session()->getId())
        );

        $this->gameId = $game->id;
    }

    /**
     * The live game instance.
     *
     * @return Game|null
     */
    #[Computed]
    public function game(): ?Game
    {
        if (!$this->gameId) {
            return null;
        }

        return Game::with(['word.subject', 'guesses'])
            ->find($this->gameId);
    }

    /**
     * The signed-in player's stats.
     *
     * @return UserStats|null
     */
    #[Computed]
    public function stats(): ?UserStats
    {
        $user = auth()->user();

        return $user ? UserStats::find($user->id) : null;
    }

    /**
     * The share of games the player has won.
     *
     * @return int
     */
    #[Computed]
    public function winRate(): int
    {
        $stats = $this->stats;

        if (!$stats || $stats->games_played < 1) {
            return 0;
        }

        return (int) round($stats->games_won / $stats->games_played * 100);
    }

    /**
     * The player's outcome for each recent puzzle.
     *
     * @return Collection
     */
    #[Computed]
    public function recentResults(): Collection
    {
        $user = auth()->user();

        if (!$user) {
            return collect();
        }

        $puzzles = DailyPuzzle::where('puzzle_date', '<=', Carbon::now()->toDateString())
            ->orderByDesc('puzzle_date')
            ->limit(self::RECENT_DAYS)
            ->get();

        $wonPuzzleIDs = Game::whereIn('daily_puzzle_id', $puzzles->pluck('id'))
            ->where('user_id', $user->id)
            ->where('mode', GameMode::Daily)
            ->where('status', GameStatus::Won)
            ->pluck('daily_puzzle_id');

        return $puzzles->reverse()
            ->values()
            ->map(fn (DailyPuzzle $puzzle) => (object) [
                'number' => $puzzle->puzzle_number,
                'won' => $wonPuzzleIDs->contains($puzzle->id),
            ]);
    }

    /**
     * The fastest solves of today's puzzle.
     *
     * @return Collection
     */
    #[Computed]
    public function topEntries(): Collection
    {
        return $this->puzzle
            ? StatsService::dailyLeaderboard($this->puzzle, self::PEEK_LIMIT)
            : collect();
    }

    /**
     * Submit a guess against the current game.
     *
     * @param string $guess
     *
     * @return void
     */
    public function submit(string $guess = ''): void
    {
        $game = $this->game;

        if (!$game || $game->isFinished()) {
            return;
        }

        $guess = strtolower(trim($guess));
        $expectedLength = Word::LENGTH;

        if (mb_strlen($guess) !== $expectedLength) {
            $this->flash = __('The guess must be exactly :count letters long.', ['count' => $expectedLength]);
            return;
        }

        try {
            GameCoordinator::submitGuess($game, $guess);
            $this->flash = null;
        } catch (ValidationException $e) {
            $this->flash = collect($e->errors())->flatten()->first();
        }

        unset($this->game);
    }

    /**
     * The share grid for the finished game.
     *
     * @return string|null
     */
    public function shareText(): ?string
    {
        $game = $this->game;

        if (!$game || !$game->shouldRevealAnswer()) {
            return null;
        }

        return ShareGridFormatter::format($game);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.minigames.kotodama.play-daily', [
            'game' => $this->game,
            'mode' => GameMode::Daily(),
            'title' => __('Kotodama · Daily #:number', ['number' => $this->puzzle?->puzzle_number ?? 0]),
            'stats' => $this->stats,
            'winRate' => $this->winRate,
            'recentResults' => $this->recentResults,
            'topEntries' => $this->topEntries,
        ]);
    }
}
