<?php

namespace App\Http\Livewire\Anime;

use App\Models\Anime;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class StarRating extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime|null
     */
    public ?Anime $anime;

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
     * @param null $anime
     * @param float|null $rating
     * @param string $starSize
     * @param bool $disabled
     *
     * @return void
     */
    function mount($anime = null, ?float $rating = null, string $starSize = 'md', bool $disabled = false)
    {
        $this->anime = $anime;
        $this->rating = $rating ?? 0.0;
        $this->starSize = match ($starSize) {
            'sm' => 'h-4',
            'md' => 'h-6',
            default => 'h-8'
        };
        $this->disabled = $disabled;
    }

    /**
     * Updates the authenticated user's rating of the anime.
     */
    public function rate()
    {
        $user = Auth::user();
        if (empty($user)) {
            return;
        }

        if ($this->rating < 0 || $this->rating > 5) {
            return;
        }

        // Update authenticated user's rating
        $user->animeRating()->updateOrCreate([
            'anime_id'  => $this->anime->id
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
        return view('livewire.anime.star-rating');
    }
}
