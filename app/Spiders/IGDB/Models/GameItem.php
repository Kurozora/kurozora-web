<?php

namespace App\Spiders\IGDB\Models;

use RoachPHP\ItemPipeline\AbstractItem;

class GameItem extends AbstractItem
{
    /**
     * The slug of the game.
     *
     * @var string
     */
    public string $slug;

    /**
     * The decoded game payload.
     *
     * @var array
     */
    public array $game;

    /**
     * Whether the game was discovered through a facet that requires the anime gate.
     *
     * @var bool
     */
    public bool $gated;

    public function __construct(string $slug, array $game, bool $gated = false)
    {
        $this->slug = $slug;
        $this->game = $game;
        $this->gated = $gated;
    }
}
