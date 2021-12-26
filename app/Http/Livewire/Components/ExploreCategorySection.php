<?php

namespace App\Http\Livewire\Components;

use App\Enums\ExploreCategoryTypes;
use App\Models\Anime;
use App\Models\Character;
use App\Models\ExploreCategory;
use App\Models\ExploreCategoryItem;
use App\Models\Genre;
use App\Models\Person;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class ExploreCategorySection extends Component
{
    /**
     * The object containing the explore category data.
     *
     * @var ExploreCategory $exploreCategory
     */
    public ExploreCategory $exploreCategory;

    /**
     * The object containing the genre data.
     *
     * @var Genre|null $genre
     */
    public ?Genre $genre = null;

    /**
     * The array containing the explore category item data.
     *
     * @var ExploreCategoryItem[] $exploreCategoryItems
     */
    public array|Collection $exploreCategoryItems = [];

    /**
     * The number of items the explore category has.
     *
     * @var int $exploreCategoryCount
     */
    public int $exploreCategoryCount = 0;

    /**
     * Prepare the component.
     *
     * @param ExploreCategory $exploreCategory
     * @param Genre|null $genre
     * @return void
     */
    public function mount(ExploreCategory $exploreCategory, ?Genre $genre = null)
    {
        $this->exploreCategory = $exploreCategory;
        $this->genre = $genre->id ? $genre : null;
        $this->exploreCategoryCount = match ($exploreCategory->type) {
            ExploreCategoryTypes::MostPopularShows => $this->genre ? Anime::whereGenre($genre)->mostPopular()->count() : Anime::mostPopular()->count(),
            ExploreCategoryTypes::UpcomingShows => $this->genre ? Anime::whereGenre($genre)->upcomingShows()->count() : Anime::upcomingShows()->count(),
            ExploreCategoryTypes::Characters => Character::bornToday()->count(),
            ExploreCategoryTypes::People => Person::bornToday()->count(),
            default => $exploreCategory->explore_category_items()->count()
        };
    }

    /**
     * Loads the explore category section.
     *
     * @return void
     */
    public function loadExploreCategoryItems()
    {
        $this->exploreCategoryItems = match ($this->exploreCategory->type) {
            ExploreCategoryTypes::MostPopularShows => $this->exploreCategory->most_popular_shows($this->genre)->explore_category_items,
            ExploreCategoryTypes::UpcomingShows => $this->exploreCategory->upcoming_shows($this->genre)->explore_category_items,
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
        return view('livewire.components.explore-category-section');
    }
}
