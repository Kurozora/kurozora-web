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
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PlayVersus extends Component
{
    public string $seed = '';
    public ?int $gameId = null;
    public ?int $challengerGameId = null;
    public ?string $flash = null;

    /**
     * Prepare the component.
     *
     * @param string $seed
     *
     * @return void
     */
    public function mount(string $seed): void
    {
        $this->seed = $seed;

        $challenger = Game::where('versus_seed', $seed)
            ->firstOrFail();

        $this->challengerGameId = $challenger->id;

        $game = GameCoordinator::startUnlimited(
            $challenger->word,
            auth()->user(),
            GameCoordinator::guestTokenFor(session()->getId())
        );
        $game->mode = GameMode::Versus();
        $game->save();

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
        return $this->gameId ? Game::with(['word.subject', 'guesses'])
            ->find($this->gameId) : null;
    }

    /**
     * The challenger's game.
     *
     * @return Game|null
     */
    #[Computed]
    public function challenger(): ?Game
    {
        return $this->challengerGameId ? Game::with(['user', 'guesses'])
            ->find($this->challengerGameId) : null;
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
        return view('livewire.minigames.kotodama.play', [
            'game' => $this->game,
            'mode' => GameMode::Versus(),
            'title' => __('Kotodama · Versus'),
            'appArgument' => 'kotodama/versus/' . $this->seed,
            'challenger' => $this->challenger,
        ]);
    }
}
