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
     * Prepare the component.
     *
     * @param ExploreCategory $exploreCategory
     *
     * @return void
     */
    public function mount(ExploreCategory $exploreCategory): void
    {
        $this->exploreCategory = $exploreCategory;
    }

    /**
     * The array containing the explore category items data.
     *
     * @return Collection exploreCategoryItems
     */
    public function getExploreCategoryItemsProperty(): \Illuminate\Support\Collection
    {
        $exploreCategoryItems = match ($this->exploreCategory->type) {
            ExploreCategoryTypes::MostPopularShows => $this->exploreCategory->most_popular_shows(),
            ExploreCategoryTypes::UpcomingShows => $this->exploreCategory->upcoming_shows(),
            ExploreCategoryTypes::NewShows => $this->exploreCategory->newShows(limit: 25),
            ExploreCategoryTypes::RecentlyUpdateShows => $this->exploreCategory->recentlyUpdatedShows(limit: 25),
            ExploreCategoryTypes::RecentlyFinishedShows => $this->exploreCategory->recentlyFinishedShows(limit: 25),
            ExploreCategoryTypes::AnimeContinuing => $this->exploreCategory->anime_continuing(),
            ExploreCategoryTypes::AnimeSeason => $this->exploreCategory->anime_season(),
            ExploreCategoryTypes::Characters => $this->exploreCategory->charactersBornToday(-1),
            ExploreCategoryTypes::People => $this->exploreCategory->peopleBornToday(-1),
            default => $this->exploreCategory
        };

        return $exploreCategoryItems->explore_category_items
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
