<?php

namespace App\Livewire\Sections;

use App\Models\Editorial;
use App\Models\MediaRating;
use App\Traits\Livewire\MediaRatingActions;
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
    use MediaRatingActions;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'review-submitted' => '$refresh',
    ];

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
     * The id of the page's review box.
     *
     * @var string|null $reviewBoxID
     */
    public ?string $reviewBoxID = null;

    /**
     * Prepare the component.
     *
     * @param Model       $model
     * @param null|string $reviewBoxId
     *
     * @return void
     */
    public function mount(Model $model, ?string $reviewBoxId = null): void
    {
        $translation = $model->translation;
        $this->model = $model->withoutRelations()
            ->setRelation('translation', $translation);
        $this->reviewBoxID = $reviewBoxId;
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
     * Returns the model's published editorial endorsement, if any.
     *
     * @return Editorial|null
     */
    public function getEditorialProperty(): ?Editorial
    {
        if (!$this->readyToLoad) {
            return null;
        }

        return $this->model->editorial()
            ->published()
            ->first();
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
            ->with(array_merge(['user.media', 'revisions'], MediaRating::lockupEagerLoads(auth()->user())))
            ->where('description', '!=', null)
            ->forReading()
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
