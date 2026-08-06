<?php

namespace App\Livewire\Components;

use App\Enums\EmojiScore;
use App\Support\UserLibraryTouch;
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
    public ?string $modelID = null;

    /**
     * The type of the model.
     *
     * @var string|null
     */
    public ?string $modelType = null;

    /**
     * The emoji score value used to fill the reaction.
     *
     * @var int|null $emojiScore
     */
    public ?int $emojiScore = null;

    /**
     * Whether interaction with the rating is disabled.
     *
     * @var bool $disabled
     */
    public bool $disabled = false;

    /**
     * Whether removing rating is allowed.
     *
     * @var bool $allowsRemove
     */
    public bool $allowsRemove = false;

    /**
     * Whether the removal confirmation is shown.
     *
     * @var bool $confirmingRemoval
     */
    public bool $confirmingRemoval = false;

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
     * @param bool        $disabled
     * @param bool        $allowsRemove
     *
     * @return void
     */
    public function mount(?string $modelId = null, ?string $modelType = null, ?float $rating = null, bool $disabled = false, bool $allowsRemove = false): void
    {
        $this->modelID = $modelId;
        $this->modelType = $modelType;
        $this->emojiScore = EmojiScore::fromRating($rating)?->value;
        $this->disabled = $disabled;
        $this->allowsRemove = $allowsRemove;
    }

    /**
     * Updates the authenticated user's rating of the model.
     *
     * @param int $value
     *
     * @return RedirectResponse|void
     */
    public function rate(int $value)
    {
        $user = auth()->user();

        if (empty($user)) {
            return to_route('sign-in');
        }

        $emojiScore = EmojiScore::coerce($value);

        if ($emojiScore === null) {
            return;
        }

        if ($this->emojiScore === $emojiScore->value) {
            if (!$this->allowsRemove) {
                return;
            }

            $mediaRating = $user->mediaRatings()->where([
                ['model_id', '=', $this->modelID],
                ['model_type', '=', $this->modelType],
            ])->first();

            // Removing the rating also deletes the review.
            if (filled($mediaRating?->description)) {
                $this->confirmingRemoval = true;
                return;
            }

            // Delete through the model so observers fire.
            $mediaRating?->delete();

            $this->emojiScore = null;

            UserLibraryTouch::touch($user->id, $this->modelType, [$this->modelID]);

            $this->dispatch($this->listenerKey(), id: $this->getID(), modelID: $this->modelID, modelType: $this->modelType, rating: null);
            return;
        }

        $rating = $emojiScore->score();

        // Update or create the authenticated user's rating for this model.
        $user->mediaRatings()
            ->updateOrCreate([
                'model_id' => $this->modelID,
                'model_type' => $this->modelType,
            ], [
                'rating' => $rating,
            ]);

        $this->emojiScore = $emojiScore->value;

        UserLibraryTouch::touch($user->id, $this->modelType, [$this->modelID]);

        $this->dispatch($this->listenerKey(), id: $this->getID(), modelID: $this->modelID, modelType: $this->modelType, rating: $rating);
    }

    /**
     * Removes the authenticated user's rating of the model.
     *
     * @return RedirectResponse|void
     */
    public function removeRating()
    {
        $user = auth()->user();

        if (empty($user)) {
            return to_route('sign-in');
        }

        // Delete through the model so observers fire.
        $user->mediaRatings()->where([
            ['model_id', '=', $this->modelID],
            ['model_type', '=', $this->modelType],
        ])->first()?->delete();

        $this->emojiScore = null;
        $this->confirmingRemoval = false;

        UserLibraryTouch::touch($user->id, $this->modelType, [$this->modelID]);

        $this->dispatch($this->listenerKey(), id: $this->getID(), modelID: $this->modelID, modelType: $this->modelType, rating: null);
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
            $this->emojiScore = EmojiScore::fromRating($rating)?->value;
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
