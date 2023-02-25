<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameViewed
{
    use Dispatchable, SerializesModels;

    /**
     * @var Game $game
     */
    public Game $game;

    /**
     * Create a new event instance.
     *
     * @param Game $game
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }
}
