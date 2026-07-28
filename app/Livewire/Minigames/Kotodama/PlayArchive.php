<?php

namespace App\Livewire\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\Word;
use App\Services\Minigames\Kotodama\GameCoordinator;
use App\Services\Minigames\Kotodama\PuzzleResolver;
use App\Services\Minigames\Kotodama\ShareGridFormatter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PlayArchive extends Component
{
    public string $date = '';
    public ?int $gameId = null;
    public ?string $flash = null;
    public ?DailyPuzzle $puzzle = null;

    /**
     * Prepare the component.
     *
     * @param string $date
     *
     * @return void
     */
    public function mount(string $date): void
    {
        $this->date = $date;

        $parsed = Carbon::parse($date);

        if (!$parsed->isPast() || $parsed->isToday()) {
            abort(404);
        }

        try {
            $this->puzzle = PuzzleResolver::archive($parsed);
        } catch (ModelNotFoundException) {
            $this->flash = __('No puzzle is available for that date.');
            return;
        }

        $game = GameCoordinator::startArchive($this->puzzle, auth()->user());
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
            'mode' => GameMode::Archive(),
            'title' => __('Kotodama · :date', ['date' => $this->date]),
            'appArgument' => 'kotodama/archive/' . $this->date,
        ]);
    }
}
