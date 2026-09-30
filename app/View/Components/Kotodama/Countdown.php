<?php

namespace App\View\Components\Kotodama;

use App\Models\Minigames\Kotodama\Game;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Countdown extends Component
{
    /**
     * The game the countdown follows.
     *
     * @var Game|null $game
     */
    public ?Game $game;

    /**
     * The epoch-ms instant when the next daily puzzle unlocks.
     *
     * @var int|null $nextPuzzleAt
     */
    public ?int $nextPuzzleAt;

    /**
     * The URL that renders the section again.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create a new component instance.
     *
     * @param Game|null $game
     * @param int|null  $nextPuzzleAt
     * @param string    $refreshUrl
     */
    public function __construct(?Game $game, ?int $nextPuzzleAt, string $refreshUrl)
    {
        $this->game = $game;
        $this->nextPuzzleAt = $nextPuzzleAt;
        $this->refreshUrl = $refreshUrl;
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.kotodama.countdown');
    }
}
