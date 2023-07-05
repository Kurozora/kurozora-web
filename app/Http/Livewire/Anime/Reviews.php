<?php

namespace App\Http\Livewire\Anime;

use App\Models\Anime;
use App\Models\MediaRating;
use App\Models\MediaStat;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Reviews extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * Whether to show the review box to the user.
     *
     * @var bool $showReviewBox
     */
    public bool $showReviewBox = false;

    /**
     * Whether to show the popup to the user.
     *
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * The written review text.
     *
     * @var string|null $reviewText
     */
    public ?string $reviewText;

    /**
     * The data used to populate the popup.
     *
     * @var array|string[]
     */
    public array $popupData = [
        'title' => '',
        'message' => '',
    ];

    /**
     * Prepare the component.
     *
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime): void
    {
        $this->anime = $anime;
    }

    /**
     * Shows the review text box to the user.
     */
    public function showReviewBox(): void
    {
        $this->reviewText = $this->userRating->description;
        $this->showReviewBox = true;
        $this->showPopup = true;
    }

    /**
     * Get the media stats.
     *
     * @return MediaStat
     */
    public function getMediaStatProperty(): MediaStat
    {
        return $this->anime->mediaStat;
    }

    /**
     * Get the media stats.
     *
     * @return LengthAwarePaginator
     */
    public function getMediaRatingsProperty(): LengthAwarePaginator
    {
        return $this->anime->mediaRatings()
            ->where('description', '!=', null)
            ->orderBy('created_at')
            ->paginate();
    }

    /**
     * Returns the user rating.
     *
     * @return MediaRating|Model|null
     */
    public function getUserRatingProperty(): MediaRating|Model|null
    {
        return $this->anime->mediaRatings()
            ->firstWhere('user_id', auth()->user()->id);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.reviews');
    }
}
