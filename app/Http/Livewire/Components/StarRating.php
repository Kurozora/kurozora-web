<?php

namespace App\Http\Livewire\Components;

use App\Models\KModel;
use App\Models\MediaRating;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;

class StarRating extends Component
{
    /**
     * The object containing the model data.
     *
     * @var KModel|null
     */
    public ?KModel $model;

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
     * Prepare the component.
     *
     * @param KModel|null $model
     * @param float|null $rating
     * @param string     $starSize
     * @param bool       $disabled
     *
     * @return void
     */
    function mount(?KModel $model, ?float $rating = null, string $starSize = 'md', bool $disabled = false): void
    {
        $this->model = $model;
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
                ['model_id', '=', $this->model->id],
                ['model_type', '=', $this->model->getMorphClass()],
            ])->forceDelete();
        } else {
            if ($this->rating < MediaRating::MIN_RATING_VALUE || $this->rating > MediaRating::MAX_RATING_VALUE) {
                return;
            }

            // Update authenticated user's rating
            $user->mediaRatings()->updateOrCreate([
                'model_id' => $this->model->id,
                'model_type' => $this->model->getMorphClass(),
            ], [
                'rating' => $this->rating
            ]);
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
