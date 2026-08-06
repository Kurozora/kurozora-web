<?php

namespace App\Livewire\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredRatingStyle;
use App\Enums\RatingStyle;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class SelectPreferredRatingStyleForm extends Component
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
     * Prepare the component.
     *
     * @param User $user
     *
     * @return void
     */
    public function mount(User $user): void
    {
        $this->state = [
            'rating_style' => $user->settings()->first()?->rating_style?->value ?? RatingStyle::Standard
        ];
    }

    /**
     * Update the user's preferred rating style.
     *
     * @param UpdatesUserPreferredRatingStyle $updater
     */
    public function updatePreferredRatingStyle(UpdatesUserPreferredRatingStyle $updater): void
    {
        $this->resetErrorBag();

        $updater->update($this->user, $this->state);

        $this->dispatch('saved');
    }

    /**
     * Get the rating styles.
     *
     * @return array
     */
    public function getRatingStylesProperty(): array
    {
        return RatingStyle::asSelectArray();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.select-preferred-rating-style-form');
    }
}
