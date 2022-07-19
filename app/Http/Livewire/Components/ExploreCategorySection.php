<?php

namespace App\Http\Livewire\Components;

use App\Enums\ExploreCategorySize;
use App\Enums\ExploreCategoryTypes;
use App\Models\Anime;
use App\Models\Character;
use App\Models\ExploreCategory;
use App\Models\ExploreCategoryItem;
use App\Models\Genre;
use App\Models\Person;
use App\Models\Theme;
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
     * The object containing the theme data.
     *
     * @var Theme|null $genre
     */
    public ?Theme $theme = null;

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
     * @param Theme|null $theme
     * @return void
     */
    public function mount(ExploreCategory $exploreCategory, ?Genre $genre = null, ?Theme $theme = null)
    {
        $this->exploreCategory = $exploreCategory;
        $mostPopularShows = null;
        $upcomingShows = null;

        if (!empty($genre->id)) {
            $this->genre = $genre;

            $anime = Anime::whereGenre($genre);
            $mostPopularShows = $anime->mostPopular()->count();
            $upcomingShows = $anime->upcomingShows()->count();
        } else {
            $this->genre = null;
        }

        if (!empty($theme->id)) {
            $this->theme = $theme;

            $anime = Anime::whereTheme($theme);
            $mostPopularShows = $anime->mostPopular()->count();
            $upcomingShows = $anime->upcomingShows()->count();
        } else {
            $this->theme = null;
        }

        $this->exploreCategoryCount = match ($exploreCategory->type) {
            ExploreCategoryTypes::MostPopularShows => $mostPopularShows ?? Anime::mostPopular()->count(),
            ExploreCategoryTypes::UpcomingShows => $upcomingShows ?? Anime::upcomingShows()->count(),
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
    public function loadExploreCategoryItems(): void
    {
        $exploreCategoryItems = match ($this->exploreCategory->type) {
            ExploreCategoryTypes::MostPopularShows => $this->exploreCategory->most_popular_shows($this->genre ?? $this->theme)->explore_category_items,
            ExploreCategoryTypes::UpcomingShows => $this->exploreCategory->upcoming_shows($this->genre ?? $this->theme)->explore_category_items,
            ExploreCategoryTypes::Characters => $this->exploreCategory->charactersBornToday()->explore_category_items->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::People => $this->exploreCategory->peopleBornToday()->explore_category_items->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            default => $this->exploreCategory->explore_category_items()->limit(10)->get()
        };

        if ($this->exploreCategory->type === ExploreCategoryTypes::Shows && $this->exploreCategory->size == ExploreCategorySize::Small) {
            $this->exploreCategoryItems = $exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            });
        } else {
            $this->exploreCategoryItems = $exploreCategoryItems;
        }
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
