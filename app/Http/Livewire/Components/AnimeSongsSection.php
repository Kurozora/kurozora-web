<?php

namespace App\Http\Livewire\Components;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AnimeSongsSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The array containing the anime songs data.
     *
     * @var array $animeSongs
     */
    public array $animeSongs = [];

    /**
     * The number of songs the anime has.
     *
     * @var int $animeSongsCount
     */
    public int $animeSongsCount;

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
        $this->animeSongsCount = $anime->getAnimeSongs()->count();
    }

    /**
     * Loads the anime songs section.
     *
     * @return void
     */
    public function loadAnimeSongs()
    {
        $this->animeSongs = $this->anime->getAnimeSongs(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime-songs-section');
    }
}
