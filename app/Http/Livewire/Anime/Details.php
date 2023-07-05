<?php

namespace App\Http\Livewire\Anime;

use App\Events\AnimeViewed;
use App\Models\Anime;
use App\Models\MediaRating;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * Whether the user has favorited the anime.
     *
     * @var bool $isFavorited
     */
    public bool $isFavorited = false;

    /**
     * Whether the user is reminded of the anime.
     *
     * @var bool $isReminded
     */
    public bool $isReminded = false;

    /**
     * Whether the user is tracking the anime.
     *
     * @var bool $isTracking
     */
    public bool $isTracking = false;

    /**
     * Whether to show the video to the user.
     *
     * @var bool $showVideo
     */
    public bool $showVideo = false;

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
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'update-anime' => 'updateAnimeHandler'
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
        // Call the AnimeViewed event
        AnimeViewed::dispatch($anime);

        $this->anime = $anime;
        $this->setupActions();
    }

    /**
     * Sets up the actions according to the user's settings.
     */
    protected function setupActions(): void
    {
        $user = auth()->user();
        if (!empty($user)) {
            $this->isTracking = $user->hasTracked($this->anime);
            $this->isFavorited = $user->hasFavorited($this->anime);
            $this->isReminded = $user->reminderAnime()->where('anime_id', $this->anime->id)->exists();
        }
    }

    /**
     * Handles the update anime vent.
     */
    public function updateAnimeHandler(): void
    {
        $this->setupActions();
    }

    /**
     * Shows the trailer video to the user.
     */
    public function showVideo(): void
    {
        $this->showVideo = true;
        $this->showPopup = true;
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
     * Adds the anime to the user's favorite list.
     */
    public function favoriteAnime(): void
    {
        $user = auth()->user();

        if ($this->isTracking) {
            if ($this->isFavorited) { // Unfavorite the show
                $user->unfavorite($this->anime);
            } else { // Favorite the show
                $user->favorite($this->anime);
            }

            $this->isFavorited = !$this->isFavorited;
        }
    }

    /**
     * Adds the anime to the user's reminder list.
     */
    public function remindAnime(): void
    {
        $user = auth()->user();

        if ($user->is_pro) {
            if ($this->isTracking) {
                if ($this->isReminded) { // Don't remind the user
                    $user->reminderAnime()->detach($this->anime->id);
                } else { // Remind the user
                    $user->reminderAnime()->attach($this->anime->id);
                }

                $this->isReminded = !$this->isReminded;
            } else {
                $this->popupData = [
                    'title' => __('Are you tracking?'),
                    'message' => __('Make sure to add the anime to your library first.'),
                ];
                $this->showPopup = true;
            }
        } else {
            $this->popupData = [
                'title' => __('That’s Unfortunate'),
                'message' => __('This feature is only accessible to pro users 🧐'),
            ];
            $this->showPopup = true;
        }
    }

    /**
     * Submits the written review.
     *
     * @return void
     */
    public function submitReview(): void
    {
        $this->userRating->update([
            'description' => e($this->reviewText)
        ]);
        $this->showReviewBox = false;
        $this->showPopup = false;
    }

    /**
     * Returns the studio relationship of the anime.
     *
     * @return Studio|null
     */
    public function getStudioProperty(): ?Studio
    {
        return $this->anime->studios()?->firstWhere('is_studio', '=', true) ?? $this->anime->studios->first();
    }

    /**
     * Returns the user rating.
     *
     * @return MediaRating|Model|null
     */
    public function getUserRatingProperty(): MediaRating|Model|null
    {
        return $this->anime->mediaRatings()->firstWhere('user_id', auth()->user()->id);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.details');
    }
}
