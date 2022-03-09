<?php

namespace App\Http\Livewire\Browse\Anime\Seasons;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Archive extends Component
{
    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount()
    {}

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.anime.seasons.archive');
    }
}
