<?php

namespace App\Livewire\Song;

use App\Models\MediaRating;
use App\Models\MediaStat;
use App\Models\Song;
use App\Traits\Livewire\WithReviewBox;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Livewire\Component;

class Reviews extends Component
{
    use WithReviewBox;

    /**
     * The object containing the song data.
     *
     * @var Song $song
     */
    public Song $song;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Get the media stats.
     *
     * @return MediaStat
     */
    public function getMediaStatProperty(): MediaStat
    {
        return $this->song->mediaStat;
    }

    /**
     * Get the media stats.
     *
     * @return Collection|CursorPaginator
     */
    public function getMediaRatingsProperty(): Collection|CursorPaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->song->mediaRatings()
            ->with(['user.media'])
            ->where('description', '!=', null)
            ->orderBy('created_at')
            ->cursorPaginate();
    }

    /**
     * Returns the user rating.
     *
     * @return MediaRating|Model|null
     */
    public function getUserRatingProperty(): MediaRating|Model|null
    {
        return $this->song->mediaRatings()
            ->firstWhere('user_id', auth()->user()?->id);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.song.reviews');
    }
}
