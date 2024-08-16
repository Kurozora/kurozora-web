<?php

namespace App\Livewire\Components;

use App\Models\MediaRating;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class StarRating extends Component
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
     * The rating used to fill the stars.
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
     * The component's listeners.
     *
     * @return array
     */
    protected function getListeners(): array
    {
        return $this->disabled ? [] : [
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
     * @param null|float  $rating
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
        $this->starSize = match ($starSize) {
            'sm' => 'h-4',
            'md' => 'h-6',
            default => 'h-8'
        };
        $this->disabled = $disabled;
    }

    /**
     * Updates the authenticated user's rating of the model.
     *
     * @return RedirectResponse|void
     */
    public function rate()
    {
        $user = auth()->user();

        if (empty($user)) {
            return to_route('sign-in');
        }

        if ($this->rating == -1) {
            $user->mediaRatings()->where([
                ['model_id', '=', $this->modelID],
                ['model_type', '=', $this->modelType],
            ])->forceDelete();
        } else {
            if ($this->rating < MediaRating::MIN_RATING_VALUE || $this->rating > MediaRating::MAX_RATING_VALUE) {
                return;
            }

            // Update authenticated user's rating
            $user->mediaRatings()->withoutGlobalScopes()
                ->updateOrCreate([
                    'model_id' => $this->modelID,
                    'model_type' => $this->modelType,
                ], [
                    'rating' => $this->rating,
                ]);
        }

        $this->dispatch($this->listenerKey(), id: $this->getID(), modelID: $this->modelID, modelType: $this->modelType, rating: $this->rating);
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
        if (
            $this->getID() != $id &&
            $modelID == $this->modelID &&
            $modelType == $this->modelType
        ) {
            $this->rating = $rating;
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.star-rating');
    }
}
