<?php

namespace App\Livewire\Components;

use App\Models\MediaRating;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class ReviewBox extends Component
{
    /**
     * The id of the review box component.
     *
     * @var string $reviewBoxID
     */
    public string $reviewBoxID;

    /**
     * The object containing the model id.
     *
     * @var string $modelID
     */
    public string $modelID;

    /**
     * The object containing the model type.
     *
     * @var string $modelType
     */
    public string $modelType;

    /**
     * The object containing the user's rating data.
     *
     * @var null|MediaRating $userRating
     */
    public ?MediaRating $userRating = null;

    /**
     * The written review text.
     *
     * @var null|string $reviewText
     */
    public ?string $reviewText;

    /**
     * The written note text.
     *
     * @var null|string $noteText
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
     * @param string           $modelId
     * @param string           $modelType
     * @param null|MediaRating $userRating
     *
     * @return void
     */
    public function mount(string $reviewBoxId, string $modelId, string $modelType, $userRating): void
    {
        $this->reviewBoxID = $reviewBoxId;
        $this->modelID = $modelId;
        $this->modelType = $modelType;
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

        $this->reviewText = $this->userRating?->description ?? '';
        $this->noteText = $this->userRating?->note ?? '';
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

        $reviewText = empty($reviewText) ? null : $reviewText;
        $noteText = empty($noteText) ? null : $noteText;

        auth()->user()->mediaRatings()
            ->updateOrCreate([
                'model_type' => $this->modelType,
                'model_id' => $this->modelID,
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
