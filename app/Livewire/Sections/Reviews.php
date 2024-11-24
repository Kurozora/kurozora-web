<?php

namespace App\Livewire\Sections;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class Reviews extends Component
{
    /**
     * The model data.
     *
     * @var Model $model
     */
    public Model $model;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Whether to show the popup to the user.
     *
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * Prepare the component.
     *
     * @param Model $model
     *
     * @return void
     */
    public function mount(Model $model): void
    {
        $translation = $model->translation;
        $this->model = $model->withoutRelations()
            ->setRelation('translation', $translation);
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Shows the popup to the user.
     *
     * @return void
     */
    public function showPopup(): void
    {
        $this->showPopup = true;
    }

    /**
     * The array of reviews.
     *
     * @return Collection
     */
    public function getReviewsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->model->mediaRatings()
            ->with(['user.media'])
            ->where('description', '!=', null)
            ->orderBy('created_at')
            ->limit(6)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.sections.reviews');
    }
}
