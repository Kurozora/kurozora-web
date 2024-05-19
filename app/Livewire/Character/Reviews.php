<?php

namespace App\Livewire\Character;

use App\Models\Character;
use App\Models\MediaRating;
use App\Models\MediaStat;
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
     * The object containing the character data.
     *
     * @var Character $character
     */
    public Character $character;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Character $character
     *
     * @return void
     */
    public function mount(Character $character): void
    {
        $this->character = $character->load(['media']);
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
        return $this->character->mediaStat;
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

        return $this->character->mediaRatings()
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
        return $this->character->mediaRatings()
            ->firstWhere('user_id', auth()->user()?->id);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.character.reviews');
    }
}
