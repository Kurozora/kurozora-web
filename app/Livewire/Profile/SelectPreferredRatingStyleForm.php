<?php

namespace App\Livewire\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredRatingStyle;
use App\Enums\RatingStyle;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
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
            'rating_style' => $user->rating_style?->value ?? RatingStyle::Standard
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
     * Get the available rating styles.
     *
     * @return Collection
     */
    public function getRatingStylesProperty(): Collection
    {
        return collect([
            [
                'value' => RatingStyle::QuickReaction,
                'name' => __('Quick Reaction'),
                'description' => __('Simple emoji-based rating'),
            ],
            [
                'value' => RatingStyle::Standard,
                'name' => __('Standard (5 Stars)'),
                'description' => __('Classic 5-star rating system'),
            ],
            [
                'value' => RatingStyle::Advanced,
                'name' => __('Advanced (10 Stars)'),
                'description' => __('More granular 10-star rating'),
            ],
            [
                'value' => RatingStyle::Detailed,
                'name' => __('Detailed Review'),
                'description' => __('Rate multiple categories'),
            ],
        ]);
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
