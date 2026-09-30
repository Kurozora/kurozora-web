<?php

namespace App\Livewire\Components;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\Word;
use App\Services\Minigames\Kotodama\GameCoordinator;
use App\Services\Minigames\Kotodama\PuzzleResolver;
use App\Services\Minigames\Kotodama\ShareGridFormatter;
use App\Traits\Livewire\WithKotodamaFlash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class KotodamaPuzzle extends Component
{
    use WithKotodamaFlash;

    /**
     * The current game ID.
     *
     * @var int|null $gameId
     */
    public ?int $gameId = null;

    /**
     * The mode the game is played in.
     *
     * @var int $mode
     */
    public int $mode;

    /**
     * Prepare the component.
     *
     * @param int|null    $gameId
     * @param int         $mode
     * @param string|null $flash
     *
     * @return void
     */
    public function mount(?int $gameId, int $mode, ?string $flash = null): void
    {
        $this->gameId = $gameId;
        $this->mode = $mode;
        $this->flash = $flash;
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
        } catch (ValidationException $exception) {
            $this->flash = collect($exception->errors())->flatten()->first();
        }

        unset($this->game);

        if ($this->game?->isFinished()) {
            $this->dispatch('kotodama-finished');
        }
    }

    /**
     * Start a new unlimited game.
     *
     * @return void
     */
    public function next(): void
    {
        if (!GameMode::fromValue($this->mode)->is(GameMode::Unlimited)) {
            return;
        }

        $word = PuzzleResolver::unlimited($this->game?->word_id);

        $game = GameCoordinator::startUnlimited(
            $word,
            auth()->user(),
            GameCoordinator::guestTokenFor(session()->getId())
        );

        $this->gameId = $game->id;
        $this->flash = null;

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
        return view('livewire.components.kotodama-puzzle', [
            'game' => $this->game,
            'gameMode' => GameMode::fromValue($this->mode),
            'shareText' => $this->shareText(),
        ]);
    }
}
