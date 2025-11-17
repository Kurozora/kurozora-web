<?php

namespace App\Livewire\Studio;

use App\Events\ModelViewed;
use App\Models\MediaRating;
use App\Models\Studio;
use App\Traits\Livewire\WithReviewBox;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    use WithReviewBox;

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
        // Call the ModelViewed event
        ModelViewed::dispatch($studio, request()->ip());

        $this->studio = $studio->load(['media', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) use ($studio) {
                return $studio->loadMissing(['mediaRatings' => function ($query) {
                    $query->where('user_id', '=', auth()->user()->id);
                }]);
            }, function() use ($studio) {
                return $studio;
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
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.studio.details');
    }
}
