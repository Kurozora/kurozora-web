<?php

namespace App\Livewire\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\Word;
use App\Services\Minigames\Kotodama\GameCoordinator;
use App\Services\Minigames\Kotodama\ShareGridFormatter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PlayUnlimited extends Component
{
    public ?int $gameId = null;
    public ?int $lastWordId = null;
    public ?string $flash = null;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->startNewGame();
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
        } catch (ValidationException $e) {
            $this->flash = collect($e->errors())->flatten()->first();
        }

        unset($this->game);
    }

    /**
     * Start a new unlimited game.
     *
     * @return void
     */
    public function next(): void
    {
        $this->startNewGame();
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
        return view('livewire.minigames.kotodama.play', [
            'game' => $this->game,
            'mode' => GameMode::Unlimited(),
            'title' => __('Kotodama · Unlimited'),
            'appArgument' => 'kotodama/unlimited',
        ]);
    }

    /**
     * Start a new unlimited game.
     *
     * @return void
     */
    protected function startNewGame(): void
    {
        $baseQuery = Word::eligibleForSchedule()
            ->with(['subject']);

        $word = (clone $baseQuery)
            ->when($this->lastWordId, fn ($query) => $query->where('id', '!=', $this->lastWordId))
            ->randomFirst();

        if (!$word) {
            $word = $baseQuery->randomFirst();
        }

        if (!$word) {
            throw (new ModelNotFoundException)->setModel(Word::class);
        }

        $game = GameCoordinator::startUnlimited(
            $word,
            auth()->user(),
            GameCoordinator::guestTokenFor(session()->getId())
        );

        $this->gameId = $game->id;
        $this->lastWordId = $word->id;
    }
}
