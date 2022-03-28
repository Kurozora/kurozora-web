<?php

namespace App\Http\Livewire\Episode;

use App\Models\Episode;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Lockup extends Component
{
    /**
     * The object containing the episode data.
     *
     * @var Episode
     */
    public Episode $episode;

    /**
     * Prepare the component.
     *
     * @param Episode $episode
     * @return void
     */
    public function mount(Episode $episode)
    {
        $this->episode = $episode;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.episode.lockup');
    }
}
