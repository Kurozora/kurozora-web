<?php

namespace App\Http\Livewire\Components;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AnimeCastSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The array containing the cast data.
     *
     * @var array $cast
     */
    public array $cast = [];

    /**
     * The number of cast the anime has.
     *
     * @var int $castCount
     */
    public int $castCount;

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
        $this->castCount = $anime->cast()->count();
    }

    /**
     * Loads the anime cast section.
     *
     * @return void
     */
    public function loadAnimeCast()
    {
        $this->cast = $this->anime->getCast(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime-cast-section');
    }
}
