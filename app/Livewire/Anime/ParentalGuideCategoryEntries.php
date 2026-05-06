<?php

namespace App\Livewire\Anime;

use App\Models\Anime;
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
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * Prepare the component.
     *
     * @param Anime  $anime
     * @param string $category
     *
     * @return void
     */
    public function mount(Anime $anime, string $category): void
    {
        $this->anime = $anime->load(['media', 'translation']);
        $this->resolveCategoryFromSlug($category);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.parental-guide-category-entries');
    }

    /**
     * @inheritDoc
     */
    protected function listingTargetModel(): Model
    {
        return $this->anime;
    }
}
