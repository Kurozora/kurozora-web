<?php

namespace App\Http\Livewire\Components;

use App\Models\KModel;
use App\Models\MediaRating;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Component;

class ReviewBox extends Component
{
    /**
     * The id of the review box component.
     *
     * @var string $reviewBoxID
     */
    public string $reviewBoxID;

    /**
     * The object containing the model data.
     *
     * @var KModel $model
     */
    public KModel $model;

    /**
     * The object containing the user's rating data.
     *
     * @var Collection|MediaRating[] $userRating
     */
    public Collection|array $userRating;

    /**
     * The written review text.
     *
     * @var string|null $reviewText
     */
    public ?string $reviewText;

    /**
     * The written note text.
     *
     * @var string|null $noteText
     */
    public ?string $noteText;

    /**
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'show-review-box' => 'handleShowReviewBox',
    ];

    /**
     * @param string           $reviewBoxId
     * @param KModel           $model
     * @param Collection|array $userRating
     *
     * @return void
     */
    public function mount(string $reviewBoxId, KModel $model, Collection|array $userRating): void
    {
        $this->reviewBoxID = $reviewBoxId;
        $this->model = $model;
        $this->userRating = $userRating;
    }

    /**
     * Handles showing the review box to the user.
     *
     * @param $id
     *
     * @return void
     */
    public function handleShowReviewBox($id): void
    {
        if ($id == $this->reviewBoxID) {
            $this->showReviewBox();
        }
    }

    /**
     * Shows the review box to the user.
     *
     * @return RedirectResponse|void
     */
    public function showReviewBox()
    {
        // Require user to authenticate if necessary.
        if (!auth()->check()) {
            return to_route('sign-in');
        }

        $this->reviewText = $this->model->mediaRatings->first()?->description;
        $this->noteText = $this->model->mediaRatings->first()?->note;
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
        $noteText = strip_tags($this->noteText);

        auth()->user()->mediaRatings()
            ->updateOrCreate([
                'model_type' => $this->model->getMorphClass(),
                'model_id' => $this->model->id,
            ], [
                'description' => $reviewText,
                'note' => $noteText,
            ]);

        $this->showPopup = false;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.review-box');
    }
}
