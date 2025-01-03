<?php

namespace App\Livewire\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredTvRating;
use App\Models\TvRating;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class SelectPreferredTvRatingForm extends Component
{
    /**
     * The user instance.
     *
     * @var User
     */
    public User $user;

    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * Determines whether to load the section.
     *
     * @var bool $readyToLoad
     */
    public $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param User $user
     *
     * @return void
     */
    public function mount(User $user): void
    {
        $this->state = [
            'tv_rating' => $user->tv_rating
        ];
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Update the user's preferred TV rating.
     *
     * @param UpdatesUserPreferredTvRating $updater
     */
    public function updatePreferredTvRating(UpdatesUserPreferredTvRating $updater): void
    {
        $this->resetErrorBag();

        $updater->update($this->user, $this->state);

        $this->dispatch('saved');
    }

    /**
     * Get the tv ratings.
     *
     * @return Collection
     */
    public function getTvRatingsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return TvRating::where('id', '!=', 1)
            ->get();
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
