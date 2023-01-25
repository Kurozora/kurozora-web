<?php

namespace App\Http\Livewire\Components\Manga;

use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AnimeRelationsSection extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * The array containing the anime relations data.
     *
     * @var array $animeRelations
     */
    public array $animeRelations = [];

    /**
     * The number of relations the manga has.
     *
     * @var int $animeRelationsCount
     */
    public int $animeRelationsCount;

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
        $this->animeRelationsCount = $this->manga->animeRelations()->count();
    }

    /**
     * Loads the anime relations section.
     *
     * @return void
     */
    public function loadAnimeRelations(): void
    {
        $this->animeRelations = $this->manga->getAnimeRelations(Manga::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.manga.anime-relations-section');
    }
}
