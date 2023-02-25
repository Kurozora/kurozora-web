<?php

namespace App\Http\Livewire\Components\Game;

use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MangaRelationsSection extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * The array containing the manga relations data.
     *
     * @var array $mangaRelations
     */
    public array $mangaRelations = [];

    /**
     * The number of relations the game has.
     *
     * @var int $mangaRelationsCount
     */
    public int $mangaRelationsCount;

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
        $this->mangaRelationsCount = $this->game->mangaRelations()->count();
    }

    /**
     * Loads the manga relations section.
     *
     * @return void
     */
    public function loadMangaRelations(): void
    {
        $this->mangaRelations = $this->game->getMangaRelations(Game::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.game.manga-relations-section');
    }
}
