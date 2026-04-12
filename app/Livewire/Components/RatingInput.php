<?php

namespace App\Livewire\Components;

use App\Enums\RatingStyle;
use App\Models\MediaRating;
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
    public ?string $modelID;

    /**
     * The type of the model.
     *
     * @var string|null
     */
    public ?string $modelType;

    /**
     * The rating used to fill the stars (internal 0-10 scale).
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
     * Whether to show the "elaborate" prompt for low standard ratings.
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
        return [
            $this->listenerKey() => 'handleRatingUpdate',
        ];
    }

    /**
     * The listener key of the component.
     *
     * @return string
     */
    protected function listenerKey(): string
    {
        return 'star-rating-updated-' . $this->modelID . '-' . $this->modelType;
    }

    /**
     * Prepare the component.
     *
     * @param null|string $modelId
     * @param null|string $modelType
     * @param null|float  $rating Internal rating value (0-10)
     * @param string      $starSize
     * @param bool        $disabled
     *
     * @return void
     */
    function mount(?string $modelId = null, ?string $modelType = null, ?float $rating = null, string $starSize = 'md', bool $disabled = false): void
    {
        $this->modelID = $modelId;
        $this->modelType = $modelType;
        $this->rating = $rating ?? MediaRating::MIN_RATING_VALUE;
        $this->starSize = $starSize;
        $this->disabled = $disabled;
    }

    /**
     * Get the user's preferred rating style.
     *
     * @return RatingStyle
     */
    public function getUserRatingStyleProperty(): RatingStyle
    {
        $user = auth()->user();

        if (empty($user) || $user->rating_style === null) {
            return RatingStyle::fromValue(RatingStyle::Standard);
        }

        return $user->rating_style;
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

            // Check if we should show the elaborate prompt
            // For standard rating, if rating is 8 or below (4 stars or less), offer to elaborate
            if ($this->userRatingStyle->value === RatingStyle::Standard && $rating !== null && $rating <= 8 && $rating > 0) {
                $this->showElaboratePrompt = true;
            } else {
                $this->showElaboratePrompt = false;
            }
        }
    }

    /**
     * Dismiss the elaborate prompt.
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
