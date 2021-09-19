<?php

namespace App\Http\Livewire\Season;

use App\Models\Season;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Episodes extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Season $season
     */
    public Season $season;

    /**
     * Prepare the component.
     *
     * @param Season $season
     *
     * @return void
     */
    public function mount(Season $season)
    {
        $this->season = $season;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.season.episodes')
            ->layout('layouts.base');
    }
}
