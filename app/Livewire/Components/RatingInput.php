<?php

namespace App\Livewire\Components;

use App\Enums\RatingStyle;
use App\Models\MediaRating;
use App\Models\RatingCategory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class RatingInput extends Component
{
    /**
     * The id the model.
     *
     * @var string|null
     */
    public ?string $modelID = null;

    /**
     * The type of the model.
     *
     * @var string|null
     */
    public ?string $modelType = null;

    /**
     * The rating used to fill the rating input.
     *
     * @var float|null $rating
     */
    public ?float $rating = null;

    /**
     * The size of the stars.
     *
     * @var string $starSize
     */
    public string $starSize = 'md';

    /**
     * Whether interaction with the rating is disabled.
     *
     * @var bool $disabled
     */
    public bool $disabled = false;

    /**
     * The id of the review box on the page.
     *
     * @var string|null $reviewBoxID
     */
    public ?string $reviewBoxID = null;

    /**
     * Whether the prompt to elaborate on the rating is shown.
     *
     * @var bool $showElaboratePrompt
     */
    public bool $showElaboratePrompt = false;

    /**
     * The component's listeners.
     *
     * @return array
     */
    protected function getListeners(): array
    {
        return $this->disabled ? [] : [
            'star-rating-updated-' . $this->modelID . '-' . $this->modelType => 'handleRatingUpdate',
        ];
    }

    /**
     * Prepare the component.
     *
     * @param null|string $modelId
     * @param null|string $modelType
     * @param null|float  $rating
     * @param string      $starSize
     * @param bool        $disabled
     * @param null|string $reviewBoxId
     *
     * @return void
     */
    public function mount(?string $modelId = null, ?string $modelType = null, ?float $rating = null, string $starSize = 'md', bool $disabled = false, ?string $reviewBoxId = null): void
    {
        $this->modelID = $modelId;
        $this->modelType = $modelType;
        $this->rating = $rating ?? MediaRating::MIN_RATING_VALUE;
        $this->starSize = $starSize;
        $this->disabled = $disabled;
        $this->reviewBoxID = $reviewBoxId;
    }

    /**
     * Get the authenticated user's preferred rating style.
     *
     * @return RatingStyle
     */
    public function getUserRatingStyleProperty(): RatingStyle
    {
        $user = auth()->user();

        if ($user === null) {
            return RatingStyle::Standard();
        }

        $user->loadMissing('settings');
        $ratingStyle = $user->settings?->rating_style ?? RatingStyle::Standard();

        if (
            $ratingStyle->is(RatingStyle::Detailed) &&
            ($this->reviewBoxID === null || !$this->hasDetailedCategories)
        ) {
            return RatingStyle::Standard();
        }

        return $ratingStyle;
    }

    /**
     * Whether detailed rating categories exist for the model type.
     *
     * @return bool
     */
    public function getHasDetailedCategoriesProperty(): bool
    {
        return RatingCategory::where('model_type', '=', $this->modelType)
            ->exists();
    }

    /**
     * Handles the event emitted when updating the rating.
     *
     * @param $id
     * @param $modelID
     * @param $modelType
     * @param $rating
     *
     * @return void
     */
    public function handleRatingUpdate($id, $modelID, $modelType, $rating): void
    {
        if ($modelID == $this->modelID && $modelType == $this->modelType) {
            $this->rating = $rating;

            $this->showElaboratePrompt = $this->reviewBoxID !== null
                && $this->userRatingStyle->is(RatingStyle::Standard)
                && $this->hasDetailedCategories
                && $rating !== null
                && $rating > MediaRating::MIN_RATING_VALUE
                && $rating <= 4.0;
        }
    }

    /**
     * Switches the user's preferred rating style to detailed and opens the review box.
     *
     * @return void
     */
    public function switchToDetailed(): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirect(route('sign-in'));
            return;
        }

        $user->settings()
            ->firstOrCreate()
            ->update([
                'rating_style' => RatingStyle::Detailed(),
            ]);
        $user->unsetRelation('settings');

        $this->showElaboratePrompt = false;

        $this->dispatch('show-review-box', id: $this->reviewBoxID);
    }

    /**
     * Dismisses the prompt to elaborate on the rating.
     *
     * @return void
     */
    public function dismissElaboratePrompt(): void
    {
        $this->showElaboratePrompt = false;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.rating-input');
    }
}
