<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BareBonesGameAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The IGDB slug of the game.
     *
     * @var string $slug
     */
    public string $slug;

    /**
     * Create a new event instance.
     *
     * @param Game $game
     */
    public function __construct(Game $game)
    {
        $this->slug = $game->igdb_slug;
    }
}
