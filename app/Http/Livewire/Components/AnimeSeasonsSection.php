<?php

namespace App\Http\Livewire\Components;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
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
    public function mount(Anime $anime): void
    {
        $this->anime = $anime;
        $this->seasonsCount = $anime->seasons()->count();
    }

    /**
     * Get the anime seasons.
     *
     * @return array|LengthAwarePaginator
     */
    public function getSeasonsProperty(): array|LengthAwarePaginator
    {
        return $this->anime->getSeasons(Anime::MAXIMUM_RELATIONSHIPS_LIMIT, reversed: true);
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
