<?php

namespace App\Http\Livewire\Components\Anime;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AnimeRelationsSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The array containing the anime relations data.
     *
     * @var array $animeRelations
     */
    public array $animeRelations = [];

    /**
     * The number of relations the anime has.
     *
     * @var int $animeRelationsCount
     */
    public int $animeRelationsCount;

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
        $this->animeRelationsCount = $this->anime->animeRelations()->count();
    }

    /**
     * Loads the anime relations section.
     *
     * @return void
     */
    public function loadAnimeRelations(): void
    {
        $this->animeRelations = $this->anime->getAnimeRelations(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime.anime-relations-section');
    }
}
