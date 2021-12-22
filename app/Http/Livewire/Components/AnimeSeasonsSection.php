<?php

namespace App\Http\Livewire\Components;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AnimeSeasonsSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The array containing the seasons data.
     *
     * @var array $seasons
     */
    public array $seasons = [];

    /**
     * The number of seasons the anime has.
     *
     * @var int $seasonsCount
     */
    public int $seasonsCount;

    /**
     * Prepare the component.
     *
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime)
    {
        $this->anime = $anime;
        $this->seasonsCount = $anime->seasons()->count();
    }

    /**
     * Loads the anime seasons section.
     *
     * @return void
     */
    public function loadAnimeSeasons()
    {
        $this->seasons = $this->anime->getSeasons(Anime::MAXIMUM_RELATIONSHIPS_LIMIT, reversed: true)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime-seasons-section');
    }
}
