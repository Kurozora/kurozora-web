<?php

namespace App\Http\Livewire\Components\Game;

use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AnimeRelationsSection extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * The array containing the anime relations data.
     *
     * @var array $animeRelations
     */
    public array $animeRelations = [];

    /**
     * The number of relations the game has.
     *
     * @var int $animeRelationsCount
     */
    public int $animeRelationsCount;

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
        $this->animeRelationsCount = $this->game->animeRelations()->count();
    }

    /**
     * Loads the anime relations section.
     *
     * @return void
     */
    public function loadAnimeRelations(): void
    {
        $this->animeRelations = $this->game->getAnimeRelations(Game::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.game.anime-relations-section');
    }
}