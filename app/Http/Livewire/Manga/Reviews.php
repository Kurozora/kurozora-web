<?php

namespace App\Http\Livewire\Manga;

use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\MediaStat;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Redirector;
use Livewire\Component;

class Reviews extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

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
     * @param Manga $manga
     *
     * @return void
     */
    public function mount(Manga $manga): void
    {
        $this->manga = $manga;
    }

    /**
     * Shows the review text box to the user.
     *
     * @return Application|RedirectResponse|Redirector|void
     */
    public function showReviewBox()
    {
        // Require user to authenticate if necessary.
        if (!auth()->check()) {
            return redirect(route('sign-in'));
        }

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
        return $this->manga->mediaStat;
    }

    /**
     * Get the media stats.
     *
     * @return LengthAwarePaginator
     */
    public function getMediaRatingsProperty(): LengthAwarePaginator
    {
        return $this->manga->mediaRatings()
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
        return $this->manga->mediaRatings()
            ->firstWhere('user_id', auth()->user()?->id);
    }

    /**
     * Submits the written review.
     *
     * @return void
     */
    public function submitReview(): void
    {
        $this->userRating->update([
            'description' => strip_tags($this->reviewText)
        ]);
        $this->showReviewBox = false;
        $this->showPopup = false;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.manga.reviews');
    }
}
