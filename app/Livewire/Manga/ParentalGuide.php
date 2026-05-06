<?php

namespace App\Livewire\Manga;

use App\Models\Manga;
use App\Models\ParentalGuideEntry;
use App\Traits\Livewire\ParentalGuideEntryActions;
use App\Traits\Livewire\ParentalGuideSubmission;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Component;

class ParentalGuide extends Component
{
    use ParentalGuideEntryActions;
    use ParentalGuideSubmission;

    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Manga $manga
     *
     * @return void
     */
    public function mount(Manga $manga): void
    {
        $authUser = auth()->user();

        $this->manga = $manga->load([
            'media',
            'translation',
            'parental_guide_entries' => function ($query) use ($authUser) {
                $query->visible()
                    ->withReason()
                    ->with(ParentalGuideEntry::lockupEagerLoads($authUser));
            },
            'parental_guide_stat'
        ]);
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
     * Get the entries grouped by category.
     *
     * @return Collection
     */
    public function getParentalGuideEntriesProperty(): Collection
    {
        $authUser = auth()->user();

        return ParentalGuideEntry::query()
            ->visible()
            ->withReason()
            ->where('model_type', '=', $this->manga->getMorphClass())
            ->where('model_id', '=', $this->manga->getKey())
            ->with(ParentalGuideEntry::lockupEagerLoads($authUser))
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('category');
    }

    /**
     * Map each category to the total number of (visible, non-empty) entries it has.
     *
     * @return Collection
     */
    public function getCategoryEntryCountsProperty(): Collection
    {
        return $this->parentalGuideEntries->map->count();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.manga.parental-guide');
    }

    /**
     * @inheritDoc
     */
    protected function submissionTargetModel(): Model
    {
        return $this->manga;
    }

    /**
     * @inheritDoc
     */
    protected function afterSubmit(): void
    {
        $authUser = auth()->user();

        $this->manga->load([
            'parental_guide_entries' => function ($query) use ($authUser) {
                $query->visible()->withReason()->with(ParentalGuideEntry::lockupEagerLoads($authUser));
            },
            'parental_guide_stat',
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function afterEntryDeleted(): void
    {
        $this->afterSubmit();
    }
}
