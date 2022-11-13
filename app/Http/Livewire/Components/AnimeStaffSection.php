<?php

namespace App\Http\Livewire\Components;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AnimeStaffSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The number of staff the anime has.
     *
     * @var int $staffCount
     */
    public int $staffCount;

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
        $this->staffCount = $anime->staff()->count();
    }

    /**
     * Loads the anime staff section.
     *
     * @return array
     */
    public function getStaffProperty(): array
    {
        return $this->anime->getStaff(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime-staff-section');
    }
}
