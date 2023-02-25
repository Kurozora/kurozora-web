<?php

namespace App\Http\Livewire\Game;

use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class StarRating extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game|null
     */
    public ?Game $game;

    /**
     * The rating used to fill the stars.
     *
     * @var float|null $rating
     */
    public ?float $rating;

    /**
     * The size of the stars.
     *
     * @var string $starSize
     */
    public string $starSize;

    /**
     * Whether interaction with the rating is disabled.
     *
     * @var bool $disabled
     */
    public bool $disabled;

    /**
     * Prepare the component.
     *
     * @param null $game
     * @param float|null $rating
     * @param string $starSize
     * @param bool $disabled
     *
     * @return void
     */
    function mount($game = null, ?float $rating = null, string $starSize = 'md', bool $disabled = false): void
    {
        $this->game = $game;
        $this->rating = $rating ?? 0.0;
        $this->starSize = match ($starSize) {
            'sm' => 'h-4',
            'md' => 'h-6',
            default => 'h-8'
        };
        $this->disabled = $disabled;
    }

    /**
     * Updates the authenticated user's rating of the game.
     */
    public function rate()
    {
        $user = auth()->user();
        if (empty($user)) {
            return;
        }

        if ($this->rating < 0 || $this->rating > 5) {
            return;
        }

        // Update authenticated user's rating
        $user->gameRatings()->updateOrCreate([
            'model_id'      => $this->game->id,
            'model_type'    => Game::class,
        ], [
            'rating'    => $this->rating
        ]);

        $this->emit('rated');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.game.star-rating');
    }
}
