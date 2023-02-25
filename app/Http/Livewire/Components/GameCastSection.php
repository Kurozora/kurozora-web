<?php

namespace App\Http\Livewire\Components;

use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GameCastSection extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * The array containing the cast data.
     *
     * @var array $gameCast
     */
    public array $gameCast = [];

    /**
     * The number of cast the game has.
     *
     * @var int $castCount
     */
    public int $castCount;

    /**
     * Prepare the component.
     *
     * @param Game $game
     *
     * @return void
     */
    public function mount(Game $game): void
    {
        $this->game = $game;
        $this->castCount = $game->cast()->count();
    }

    /**
     * Loads the game cast section.
     *
     * @return void
     */
    public function loadGameCast(): void
    {
        $this->gameCast = $this->game->getCast(Game::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.game-cast-section');
    }
}
