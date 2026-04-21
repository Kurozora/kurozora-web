<?php

namespace App\Livewire\Components;

use App\Enums\RatingStyle;
use App\Models\MediaRating;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class QuickReactionRating extends Component
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
     * The quick reaction value (-1, 0, 1, or null for not rated).
     *
     * @var int|null $reaction
     */
    public ?int $reaction;

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
        return 'quick-reaction-updated-' . $this->modelID . '-' . $this->modelType;
    }

    /**
     * Prepare the component.
     *
     * @param null|string $modelId
     * @param null|string $modelType
     * @param null|float  $rating Internal rating value (0-10)
     * @param bool        $disabled
     *
     * @return void
     */
    function mount(?string $modelId = null, ?string $modelType = null, ?float $rating = null, bool $disabled = false): void
    {
        $this->modelID = $modelId;
        $this->modelType = $modelType;
        $this->reaction = $rating !== null ? RatingStyle::ratingToQuickReaction($rating) : null;
        $this->disabled = $disabled;
    }

    /**
     * Updates the authenticated user's rating of the model.
     *
     * @param int $value The reaction value (-1, 0, 1, or -2 to remove)
     *
     * @return RedirectResponse|void
     */
    public function rate(int $value)
    {
        $user = auth()->user();

        if (empty($user)) {
            return to_route('sign-in');
        }

        if ($value == -2) {
            // Remove rating
            $user->mediaRatings()->where([
                ['model_id', '=', $this->modelID],
                ['model_type', '=', $this->modelType],
            ])->forceDelete();
            $this->reaction = null;
        } else {
            if (!in_array($value, [-1, 0, 1])) {
                return;
            }

            $internalRating = RatingStyle::quickReactionToRating($value);

            // Update authenticated user's rating
            $user->mediaRatings()->withoutGlobalScopes()
                ->updateOrCreate([
                    'model_id' => $this->modelID,
                    'model_type' => $this->modelType,
                ], [
                    'rating' => $internalRating,
                    'rating_style' => RatingStyle::QuickReaction,
                ]);

            $this->reaction = $value;
        }

        $this->dispatch($this->listenerKey(), id: $this->getID(), modelID: $this->modelID, modelType: $this->modelType, reaction: $this->reaction);
        // Also dispatch the standard star-rating event for other components
        $this->dispatch('star-rating-updated-' . $this->modelID . '-' . $this->modelType, id: $this->getID(), modelID: $this->modelID, modelType: $this->modelType, rating: $this->reaction !== null ? RatingStyle::quickReactionToRating($this->reaction) : null);
    }

    /**
     * Handles the event emitted when updating the rating.
     *
     * @param $id
     * @param $modelID
     * @param $modelType
     * @param $reaction
     *
     * @return void
     */
    public function handleRatingUpdate($id, $modelID, $modelType, $reaction): void
    {
        if (
            $this->getID() != $id &&
            $modelID == $this->modelID &&
            $modelType == $this->modelType
        ) {
            $this->reaction = $reaction;
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.quick-reaction-rating');
    }
}
