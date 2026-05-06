<?php

namespace App\Livewire\Manga;

use App\Models\Manga;
use App\Traits\Livewire\ParentalGuideCategoryListing;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ParentalGuideCategoryEntries extends Component
{
    use ParentalGuideCategoryListing;

    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * Prepare the component.
     *
     * @param Manga  $manga
     * @param string $category
     *
     * @return void
     */
    public function mount(Manga $manga, string $category): void
    {
        $this->manga = $manga->load(['media', 'translation']);
        $this->resolveCategoryFromSlug($category);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.manga.parental-guide-category-entries');
    }

    /**
     * @inheritDoc
     */
    protected function listingTargetModel(): Model
    {
        return $this->manga;
    }
}
