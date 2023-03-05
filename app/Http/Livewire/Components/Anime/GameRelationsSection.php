<?php

namespace App\Http\Livewire\Components\Anime;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GameRelationsSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The array containing the game relations data.
     *
     * @var array $gameRelations
     */
    public array $gameRelations = [];

    /**
     * The number of relations the game has.
     *
     * @var int $gameRelationsCount
     */
    public int $gameRelationsCount;

    /**
     * Prepare the component.
     *
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime): void
    {
        $this->anime = $anime;
        $this->gameRelationsCount = $this->anime->gameRelations()->count();
    }

    /**
     * Loads the game relations section.
     *
     * @return void
     */
    public function loadGameRelations(): void
    {
        $this->gameRelations = $this->anime->getGameRelations(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime.game-relations-section');
    }
}
