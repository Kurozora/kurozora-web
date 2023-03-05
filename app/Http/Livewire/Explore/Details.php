<?php

namespace App\Http\Livewire\Explore;

use App\Enums\ExploreCategoryTypes;
use App\Models\ExploreCategory;
use App\Traits\Livewire\WithPagination;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Details extends Component
{
    use WithPagination;

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
            ExploreCategoryTypes::MostPopularShows => $this->exploreCategory->mostPopularShows(),
            ExploreCategoryTypes::UpcomingShows => $this->exploreCategory->upcomingShows(),
            ExploreCategoryTypes::NewShows => $this->exploreCategory->newShows(limit: 25),
            ExploreCategoryTypes::RecentlyUpdateShows => $this->exploreCategory->recentlyUpdatedShows(limit: 25),
            ExploreCategoryTypes::RecentlyFinishedShows => $this->exploreCategory->recentlyFinishedShows(limit: 25),
            ExploreCategoryTypes::ContinuingShows => $this->exploreCategory->animeContinuing(),
            ExploreCategoryTypes::ShowsSeason => $this->exploreCategory->animeSeason(),
            ExploreCategoryTypes::MostPopularLiteratures => $this->exploreCategory->mostPopularLiterature(),
            ExploreCategoryTypes::UpcomingLiteratures => $this->exploreCategory->upcomingLiterature(),
            ExploreCategoryTypes::NewLiteratures => $this->exploreCategory->newLiterature(limit: 25),
            ExploreCategoryTypes::RecentlyUpdateLiteratures => $this->exploreCategory->recentlyUpdatedLiterature(limit: 25),
            ExploreCategoryTypes::RecentlyFinishedLiteratures => $this->exploreCategory->recentlyFinishedLiterature(limit: 25),
            ExploreCategoryTypes::ContinuingLiteratures => $this->exploreCategory->literatureContinuing(),
            ExploreCategoryTypes::LiteraturesSeason => $this->exploreCategory->literatureSeason(),
            ExploreCategoryTypes::MostPopularGames => $this->exploreCategory->mostPopularGames(),
            ExploreCategoryTypes::UpcomingGames => $this->exploreCategory->upcomingGames(),
            ExploreCategoryTypes::NewGames => $this->exploreCategory->newGames(limit: 25),
            ExploreCategoryTypes::RecentlyUpdateGames => $this->exploreCategory->recentlyUpdatedGames(limit: 25),
            ExploreCategoryTypes::GamesSeason => $this->exploreCategory->gamesSeason(),
            ExploreCategoryTypes::Characters => $this->exploreCategory->charactersBornToday(-1),
            ExploreCategoryTypes::People => $this->exploreCategory->peopleBornToday(-1),
            default => $this->exploreCategory
        };

        return $exploreCategoryItems->exploreCategoryItems
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
