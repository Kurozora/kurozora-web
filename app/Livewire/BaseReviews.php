<?php

namespace App\Livewire;

use App\Models\MediaRating;
use App\Models\MediaStat;
use App\Traits\Livewire\MediaRatingActions;
use App\Traits\Livewire\WithReviewBox;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Livewire\Component;

abstract class BaseReviews extends Component
{
    use MediaRatingActions,
        WithReviewBox;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'review-submitted' => '$refresh',
    ];

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = '';

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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
     * The model the reviews belong to.
     *
     * @return Model
     */
    abstract protected function reviewable(): Model;

    /**
     * Get the media stats.
     *
     * @return MediaStat
     */
    public function getMediaStatProperty(): MediaStat
    {
        return $this->reviewable()->mediaStat;
    }

    /**
     * Get the media ratings.
     *
     * @return Collection|CursorPaginator
     */
    public function getMediaRatingsProperty(): Collection|CursorPaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->reviewable()->mediaRatings()
            ->with(array_merge(['user.media'], MediaRating::lockupEagerLoads(auth()->user())))
            ->where('description', '!=', null)
            ->orderBy('created_at')
            ->cursorPaginate();
    }

    /**
     * Returns the user rating.
     *
     * @return MediaRating|Model|null
     */
    public function getUserRatingProperty(): MediaRating|Model|null
    {
        return $this->reviewable()->mediaRatings()
            ->firstWhere('user_id', auth()->user()?->id);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view($this->view);
    }
}
