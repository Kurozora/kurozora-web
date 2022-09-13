<?php

namespace App\Http\Livewire\Song;

use App\Models\Song;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the song data.
     *
     * @var Song $song
     */
    public Song $song;

    /**
     * Prepare the component.
     *
     * @param Song $song
     * @return void
     */
    public function mount(Song $song): void
    {
        $this->song = $song;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.song.details');
    }
}
