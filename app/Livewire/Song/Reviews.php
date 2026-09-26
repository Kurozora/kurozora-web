<?php

namespace App\Livewire\Song;

use App\Livewire\BaseReviews;
use App\Models\Song;
use Illuminate\Database\Eloquent\Model;

class Reviews extends BaseReviews
{
    /**
     * The object containing the song data.
     *
     * @var Song $song
     */
    public Song $song;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.song.reviews';

    /**
     * Prepare the component.
     *
     * @param Song $song
     *
     * @return void
     */
    public function mount(Song $song): void
    {
        $this->song = $song->load(['media']);
    }

    /**
     * The song the reviews belong to.
     *
     * @return Model
     */
    protected function reviewable(): Model
    {
        return $this->song;
    }
}
