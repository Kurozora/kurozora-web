<?php

namespace App\Livewire\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\GameMode;
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
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PlayDaily extends Component
{
    const int PEEK_LIMIT = 3;

    /**
     * Whether to show the how-to-play modal.
     *
     * @var bool $showHelp
     */
    public bool $showHelp = false;

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

        $game = GameCoordinator::startDaily($this->puzzle, auth()->user());

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
     * The epoch-ms instant when the next daily puzzle unlocks.
     *
     * @return int|null
     */
    public function nextPuzzleAt(): ?int
    {
        return $this->puzzle?->puzzle_date?->copy()->addDay()->startOfDay()->getTimestampMs();
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
            'topEntries' => $this->topEntries,
            'nextPuzzleAt' => $this->nextPuzzleAt(),
        ]);
    }
}
