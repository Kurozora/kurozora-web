<?php

namespace App\Http\Livewire\Explore;

use App\Enums\ExploreCategoryTypes;
use App\Models\ExploreCategory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the explore category data.
     *
     * @var ExploreCategory
     */
    public ExploreCategory $exploreCategory;

    /**
     * The array containing the explore category items data.
     *
     * @var Collection exploreCategoryItems
     */
    public Collection $exploreCategoryItems;

    /**
     * Prepare the component.
     *
     * @param ExploreCategory $exploreCategory
     *
     * @return void
     */
    public function mount(ExploreCategory $exploreCategory)
    {
        $this->exploreCategory = $exploreCategory;
        $this->exploreCategoryItems = match ($this->exploreCategory->type) {
            ExploreCategoryTypes::MostPopularShows => $this->exploreCategory->most_popular_shows()->explore_category_items,
            ExploreCategoryTypes::UpcomingShows => $this->exploreCategory->upcoming_shows()->explore_category_items,
            ExploreCategoryTypes::Characters => $this->exploreCategory->charactersBornToday()->explore_category_items,
            ExploreCategoryTypes::People => $this->exploreCategory->peopleBornToday()->explore_category_items,
            default => $this->exploreCategory->explore_category_items
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.explore.details');
    }
}
