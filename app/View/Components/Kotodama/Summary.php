<?php

namespace App\View\Components\Kotodama;

use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\UserStats;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Summary extends Component
{
    /**
     * The game the summary belongs to.
     *
     * @var Game|null $game
     */
    public ?Game $game;

    /**
     * The signed-in player's stats.
     *
     * @var UserStats|null $stats
     */
    public ?UserStats $stats;

    /**
     * The share of games the player has won.
     *
     * @var int $winRate
     */
    public int $winRate;

    /**
     * The fastest solves of today's puzzle.
     *
     * @var Collection $topEntries
     */
    public Collection $topEntries;

    /**
     * The URL that renders the section again.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create a new component instance.
     *
     * @param Game|null      $game
     * @param UserStats|null $stats
     * @param int            $winRate
     * @param Collection     $topEntries
     * @param string         $refreshUrl
     */
    public function __construct(?Game $game, ?UserStats $stats, int $winRate, Collection $topEntries, string $refreshUrl)
    {
        $this->game = $game;
        $this->stats = $stats;
        $this->winRate = $winRate;
        $this->topEntries = $topEntries;
        $this->refreshUrl = $refreshUrl;
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.kotodama.summary');
    }
}
