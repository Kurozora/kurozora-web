<?php

namespace App\Http\Livewire\Components;

use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GameStudiosSection extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * The array containing the studios data.
     *
     * @var array $studios
     */
    public array $studios = [];

    /**
     * The number of studios the game has.
     *
     * @var int $studiosCount
     */
    public int $studiosCount;

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
        $this->studiosCount = $game->studios()->count();
    }

    /**
     * Loads the media studios section.
     *
     * @return void
     */
    public function loadGameStudios(): void
    {
        $this->studios = $this->game->getStudios(Game::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.game-studios-section');
    }
}
