<?php

namespace App\Http\Livewire\Components;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AnimeStudiosSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The array containing the studios data.
     *
     * @var array $studios
     */
    public array $studios = [];

    /**
     * The number of studios the anime has.
     *
     * @var int $studiosCount
     */
    public int $studiosCount;

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
        $this->studiosCount = $anime->studios()->count();
    }

    /**
     * Loads the anime studios section.
     *
     * @return void
     */
    public function loadAnimeStudios(): void
    {
        $this->studios = $this->anime->getStudios(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime-studios-section');
    }
}
