<?php

namespace App\Http\Livewire\Song;

use App\Events\SongViewed;
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
     * Whether to show the share popup to the user.
     *
     * @var bool $showSharePopup
     */
    public bool $showSharePopup = false;

    /**
     * Prepare the component.
     *
     * @param Song $song
     * @return void
     */
    public function mount(Song $song): void
    {
        // Call the SongViewed event
        SongViewed::dispatch($song);

        $this->song = $song->load(['media']);
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
