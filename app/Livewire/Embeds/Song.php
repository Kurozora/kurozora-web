<?php

namespace App\Livewire\Embeds;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Song extends Component
{
    /**
     * The object containing the song data.
     *
     * @var \App\Models\Song $song
     */
    public \App\Models\Song $song;

    /**
     * Prepare the component.
     *
     * @param \App\Models\Song $song
     * @return void
     */
    public function mount(\App\Models\Song $song): void
    {
        $this->song = $song->load(['media']);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.embeds.song')
            ->layout('components.layouts.embed');
    }
}
