<?php

namespace App\Http\Livewire\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredTvRating;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class SelectPreferredTvRatingForm extends Component
{
    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount()
    {
        $this->state = [
            'tv_rating' => settings('tv_rating')
        ];
    }

    /**
     * Update the user's preferred tv rating.
     *
     * @param UpdatesUserPreferredTvRating $updater
     */
    public function updatePreferredTvRating(UpdatesUserPreferredTvRating $updater)
    {
        $this->resetErrorBag();

        $updater->update(Auth::user(), $this->state);

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.select-preferred-tv-rating-form');
    }
}
