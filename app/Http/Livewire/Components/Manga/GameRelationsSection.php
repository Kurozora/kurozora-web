<?php

namespace App\Http\Livewire\Components\Manga;

use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GameRelationsSection extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * The array containing the game relations data.
     *
     * @var array $gameRelations
     */
    public array $gameRelations = [];

    /**
     * The number of relations the manga has.
     *
     * @var int $gameRelationsCount
     */
    public int $gameRelationsCount;

    /**
     * Prepare the component.
     *
     * @param Manga $manga
     *
     * @return void
     */
    public function mount(Manga $manga): void
    {
        $this->manga = $manga;
        $this->gameRelationsCount = $this->manga->gameRelations()->count();
    }

    /**
     * Loads the game relations section.
     *
     * @return void
     */
    public function loadGameRelations(): void
    {
        $this->gameRelations = $this->manga->getGameRelations(Manga::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.manga.game-relations-section');
    }
}
