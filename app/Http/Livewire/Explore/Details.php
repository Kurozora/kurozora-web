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
    public function mount(ExploreCategory $exploreCategory): void
    {
        $this->exploreCategory = $exploreCategory;
        $exploreCategoryItems = match ($exploreCategory->type) {
            ExploreCategoryTypes::MostPopularShows => $exploreCategory->most_popular_shows(),
            ExploreCategoryTypes::UpcomingShows => $exploreCategory->upcoming_shows(),
            ExploreCategoryTypes::Characters => $exploreCategory->charactersBornToday(-1),
            ExploreCategoryTypes::People => $exploreCategory->peopleBornToday(-1),
            default => $exploreCategory
        };
        $this->exploreCategoryItems = $exploreCategoryItems->explore_category_items
            ->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            });
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
