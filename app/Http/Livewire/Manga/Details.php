<?php

namespace App\Http\Livewire\Manga;

use App\Events\MangaViewed;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * Whether the user has favorited the manga.
     *
     * @var bool $isFavorited
     */
    public bool $isFavorited = false;

    /**
     * Whether the user is reminded of the manga.
     *
     * @var bool $isReminded
     */
    public bool $isReminded = false;

    /**
     * Whether the user is tracking the manga.
     *
     * @var bool $isTracking
     */
    public bool $isTracking = false;

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
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'update-manga' => 'updateMangaHandler'
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
        // Call the MangaViewed event
        MangaViewed::dispatch($manga);

        $this->manga = $manga->load(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

        $this->setupActions();
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
     * Sets up the actions according to the user's settings.
     */
    protected function setupActions(): void
    {
        $user = auth()->user();
        if (!empty($user)) {
            $this->isTracking = $user->hasTracked($this->manga);
            $this->isFavorited = $user->hasFavorited($this->manga);
//            $this->isReminded = $user->reminderManga()->where('manga_id', $this->manga->id)->exists();
        }
    }

    /**
     * Handles the update manga vent.
     */
    public function updateMangaHandler(): void
    {
        $this->setupActions();
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
     * Adds the manga to the user's favorite list.
     */
    public function favoriteManga(): void
    {
        $user = auth()->user();

        if ($this->isTracking) {
            if ($this->isFavorited) { // Unfavorite the show
                $user->unfavorite($this->manga);
            } else { // Favorite the show
                $user->favorite($this->manga);
            }

            $this->isFavorited = !$this->isFavorited;
        }
    }

    /**
     * Adds the manga to the user's reminder list.
     */
    public function remindManga(): void
    {
        $user = auth()->user();

        if ($user->is_subscribed) {
            if ($this->isTracking) {
//                if ($this->isReminded) { // Don't remind the user
//                    $user->reminderManga()->detach($this->manga->id);
//                } else { // Remind the user
//                    $user->reminderManga()->attach($this->manga->id);
//                }

                $this->isReminded = !$this->isReminded;
            } else {
                $this->popupData = [
                    'title' => __('Are you tracking?'),
                    'message' => __('Make sure to add the manga to your library first.'),
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
            'description' => strip_tags($this->reviewText)
        ]);
        $this->showReviewBox = false;
        $this->showPopup = false;
    }

    /**
     * Returns the studio relationship of the manga.
     *
     * @return Studio|null
     */
    public function getStudioProperty(): ?Studio
    {
        if (!$this->readyToLoad) {
            return null;
        }

        return $this->manga->studios()?->firstWhere('is_studio', '=', true) ?? $this->manga->studios->first();
    }

    /**
     * Returns the user rating.
     *
     * @return MediaRating|Model|null
     */
    public function getUserRatingProperty(): MediaRating|Model|null
    {
        return $this->manga->mediaRatings()->firstWhere('user_id', auth()->user()?->id);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.manga.details');
    }
}
