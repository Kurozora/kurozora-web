<?php

namespace App\Http\Livewire\Components\Game;

use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class ReviewsSection extends Component
{
    /**
     * The game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Whether to show the popup to the user.
     *
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadReviews(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Shows the popup to the user.
     *
     * @return void
     */
    public function showPopup(): void
    {
        $this->showPopup = true;
    }

    /**
     * The array of reviews.
     *
     * @return LengthAwarePaginator|array
     */
    public function getReviewsProperty(): LengthAwarePaginator|array
    {
        if (!$this->readyToLoad) {
            return [];
        }

        return $this->game->mediaRatings()
            ->where('description', '!=', null)
            ->orderBy('created_at')
            ->limit(6)
            ->paginate();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.game.reviews-section');
    }
}
