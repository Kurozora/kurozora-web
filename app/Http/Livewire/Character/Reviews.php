<?php

namespace App\Http\Livewire\Character;

use App\Models\Character;
use App\Models\MediaRating;
use App\Models\MediaStat;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Livewire\Component;

class Reviews extends Component
{
    /**
     * The object containing the character data.
     *
     * @var Character $character
     */
    public Character $character;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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
     * @param Character $character
     *
     * @return void
     */
    public function mount(Character $character): void
    {
        $this->character = $character->load(['media']);
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Shows the review text box to the user.
     *
     * @return RedirectResponse|void
     */
    public function showReviewBox()
    {
        // Require user to authenticate if necessary.
        if (!auth()->check()) {
            return to_route('sign-in');
        }

        $this->reviewText = $this->userRating?->description;
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
        return $this->character->mediaStat;
    }

    /**
     * Get the media stats.
     *
     * @return Collection|CursorPaginator
     */
    public function getMediaRatingsProperty(): Collection|CursorPaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->character->mediaRatings()
            ->with(['user.media'])
            ->where('description', '!=', null)
            ->orderBy('created_at')
            ->cursorPaginate();
    }

    /**
     * Returns the user rating.
     *
     * @return MediaRating|Model|null
     */
    public function getUserRatingProperty(): MediaRating|Model|null
    {
        return $this->character->mediaRatings()
            ->firstWhere('user_id', auth()->user()?->id);
    }

    /**
     * Submits the written review.
     *
     * @return void
     */
    public function submitReview(): void
    {
        $reviewText = strip_tags($this->reviewText);

        auth()->user()->mediaRatings()
            ->updateOrCreate([
                'model_type' => $this->character->getMorphClass(),
                'model_id' => $this->character->id,
            ], [
                'description' => $reviewText,
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
        return view('livewire.character.reviews');
    }
}
