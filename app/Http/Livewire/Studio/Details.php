<?php

namespace App\Http\Livewire\Studio;

use App\Events\StudioViewed;
use App\Models\MediaRating;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the studio data.
     *
     * @var Studio $studio
     */
    public Studio $studio;

    /**
     * The object containing the user's rating data.
     *
     * @var Collection|MediaRating[] $userRating
     */
    public Collection|array $userRating;

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
        'type' => 'default'
    ];

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Studio $studio
     *
     * @return void
     */
    public function mount(Studio $studio): void
    {
        // Call the StudioViewed event
        StudioViewed::dispatch($studio);

        $this->studio = $studio->load(['media'])
            ->when(auth()->user(), function ($query, $user) use ($studio) {
                return $studio->loadMissing(['mediaRatings' => function ($query) {
                    $query->where('user_id', '=', auth()->user()->id);
                }]);
            });

        if (!auth()->check()) {
            $this->studio->setRelation('mediaRatings', collect());
        }

        $this->userRating = $studio->mediaRatings;
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

        $this->reviewText = $this->studio->mediaRatings->first()?->description;
        $this->showReviewBox = true;
        $this->showPopup = true;
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
                'model_type' => $this->studio->getMorphClass(),
                'model_id' => $this->studio->id,
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
        return view('livewire.studio.details');
    }
}
