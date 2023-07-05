<?php

namespace App\Http\Livewire\Anime;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
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
    function mount($anime = null, ?float $rating = null, string $starSize = 'md', bool $disabled = false): void
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
     *
     * @return Application|RedirectResponse|Redirector|void
     */
    public function rate()
    {
        $user = auth()->user();
        if (empty($user)) {
            return redirect(route('sign-in'));
        }

        if ($this->rating == -1) {
            $user->animeRatings()->where([
                ['model_id', '=', $this->anime->id],
                ['model_type', '=', Anime::class],
            ])->forceDelete();
        } else {
            if ($this->rating < 0 || $this->rating > 5) {
                return;
            }

            // Update authenticated user's rating
            $user->animeRatings()->updateOrCreate([
                'model_id' => $this->anime->id,
                'model_type' => Anime::class,
            ], [
                'rating' => $this->rating
            ]);
        }
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
