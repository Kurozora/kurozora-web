<?php

namespace App\Http\Livewire\Components;

use App\Enums\ExploreCategorySize;
use App\Enums\ExploreCategoryTypes;
use App\Models\Anime;
use App\Models\Character;
use App\Models\ExploreCategory;
use App\Models\Genre;
use App\Models\Manga;
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
     * The number of items the explore category has.
     *
     * @var int $exploreCategoryCount
     */
    public int $exploreCategoryCount = 0;

    /**
     * Whether the component is initialized.
     *
     * @var bool $isInit
     */
    public bool $isInit = false;

    /**
     * Prepare the component.
     *
     * @param ExploreCategory $exploreCategory
     * @param Genre|null $genre
     * @param Theme|null $theme
     * @return void
     */
    public function mount(ExploreCategory $exploreCategory, ?Genre $genre = null, ?Theme $theme = null): void
    {
        $this->exploreCategory = $exploreCategory;

        if (!empty($genre->id)) {
            $this->genre = $genre;
            $this->exploreCategoryCount = match ($exploreCategory->type) {
                ExploreCategoryTypes::MostPopularShows => Anime::whereGenre($genre)
                    ->mostPopular()
                    ->count(),
                ExploreCategoryTypes::UpcomingShows => Anime::whereGenre($genre)
                    ->upcomingShows()
                    ->count(),
                ExploreCategoryTypes::NewShows => Anime::whereGenre($genre)
                    ->newShows()
                    ->count(),
                ExploreCategoryTypes::RecentlyUpdateShows => Anime::whereGenre($genre)
                    ->recentlyUpdatedShows()
                    ->count(),
                ExploreCategoryTypes::RecentlyFinishedShows => Anime::whereGenre($genre)
                    ->recentlyFinishedShows()
                    ->count(),
                ExploreCategoryTypes::ContinuingShows => Anime::whereGenre($genre)
                    ->animeContinuing()
                    ->count(),
                ExploreCategoryTypes::ShowsSeason => Anime::whereGenre($genre)
                    ->animeSeason()
                    ->count(),
                ExploreCategoryTypes::MostPopularLiteratures => Manga::whereGenre($genre)
                    ->mostPopular()
                    ->count(),
                ExploreCategoryTypes::UpcomingLiteratures => Manga::whereGenre($genre)
                    ->upcomingManga()
                    ->count(),
                ExploreCategoryTypes::NewLiteratures => Manga::whereGenre($genre)
                    ->newManga()
                    ->count(),
                ExploreCategoryTypes::RecentlyUpdateLiteratures => Manga::whereGenre($genre)
                    ->recentlyUpdatedManga()
                    ->count(),
                ExploreCategoryTypes::RecentlyFinishedLiteratures => Manga::whereGenre($genre)
                    ->recentlyFinishedManga()
                    ->count(),
                ExploreCategoryTypes::ContinuingLiteratures => Manga::whereGenre($genre)
                    ->mangaContinuing()
                    ->count(),
                ExploreCategoryTypes::LiteraturesSeason => Manga::whereGenre($genre)
                    ->mangaSeason()
                    ->count(),
                default => 0
            };
            return;
        } else {
            $this->genre = null;
        }

        if (!empty($theme->id)) {
            $this->theme = $theme;
            $this->exploreCategoryCount = match ($exploreCategory->type) {
                ExploreCategoryTypes::MostPopularShows => Anime::whereTheme($theme)
                    ->mostPopular()
                    ->count(),
                ExploreCategoryTypes::UpcomingShows => Anime::whereTheme($theme)
                    ->upcomingShows()
                    ->count(),
                ExploreCategoryTypes::NewShows => Anime::whereTheme($theme)
                    ->newShows()
                    ->count(),
                ExploreCategoryTypes::RecentlyUpdateShows => Anime::whereTheme($theme)
                    ->recentlyUpdatedShows()
                    ->count(),
                ExploreCategoryTypes::RecentlyFinishedShows => Anime::whereTheme($theme)
                    ->recentlyFinishedShows()
                    ->count(),
                ExploreCategoryTypes::ContinuingShows => Anime::whereTheme($theme)
                    ->animeContinuing()
                    ->count(),
                ExploreCategoryTypes::ShowsSeason => Anime::whereTheme($theme)
                    ->animeSeason()
                    ->count(),
                ExploreCategoryTypes::MostPopularLiteratures => Manga::whereTheme($theme)
                    ->mostPopular()
                    ->count(),
                ExploreCategoryTypes::UpcomingLiteratures => Manga::whereTheme($theme)
                    ->upcomingManga()
                    ->count(),
                ExploreCategoryTypes::NewLiteratures => Manga::whereTheme($theme)
                    ->newManga()
                    ->count(),
                ExploreCategoryTypes::RecentlyUpdateLiteratures => Manga::whereTheme($theme)
                    ->recentlyUpdatedManga()
                    ->count(),
                ExploreCategoryTypes::RecentlyFinishedLiteratures => Manga::whereTheme($theme)
                    ->recentlyFinishedManga()
                    ->count(),
                ExploreCategoryTypes::ContinuingLiteratures => Manga::whereTheme($theme)
                    ->mangaContinuing()
                    ->count(),
                ExploreCategoryTypes::LiteraturesSeason => Manga::whereTheme($theme)
                    ->mangaSeason()
                    ->count(),
                default => 0
            };
            return;
        } else {
            $this->theme = null;
        }

        $this->exploreCategoryCount = match ($exploreCategory->type) {
            ExploreCategoryTypes::MostPopularShows => Anime::mostPopular()->count(),
            ExploreCategoryTypes::UpcomingShows => Anime::upcomingShows()->count(),
            ExploreCategoryTypes::NewShows => Anime::newShows()->count(),
            ExploreCategoryTypes::RecentlyUpdateShows => Anime::recentlyUpdatedShows()->count(),
            ExploreCategoryTypes::RecentlyFinishedShows => Anime::recentlyFinishedShows()->count(),
            ExploreCategoryTypes::ContinuingShows => Anime::animeContinuing()->count(),
            ExploreCategoryTypes::ShowsSeason => Anime::animeSeason()->count(),
            ExploreCategoryTypes::MostPopularLiteratures => Manga::mostPopular()->count(),
            ExploreCategoryTypes::UpcomingLiteratures => Manga::upcomingManga()->count(),
            ExploreCategoryTypes::NewLiteratures => Manga::newManga()->count(),
            ExploreCategoryTypes::RecentlyUpdateLiteratures => Manga::recentlyUpdatedManga()->count(),
            ExploreCategoryTypes::RecentlyFinishedLiteratures => Manga::recentlyFinishedManga()->count(),
            ExploreCategoryTypes::ContinuingLiteratures => Manga::mangaContinuing()->count(),
            ExploreCategoryTypes::LiteraturesSeason => Manga::mangaSeason()->count(),
            ExploreCategoryTypes::Characters => Character::bornToday()->count(),
            ExploreCategoryTypes::People => Person::bornToday()->count(),
            default => $exploreCategory->exploreCategoryItems()->count()
        };
    }

    /**
     * Loads the explore category section.
     *
     * @return void
     */
    public function loadExploreCategoryItems(): void
    {
        $this->isInit = true;
    }

    /**
     * The array containing the explore category item data.
     *
     * @return array|Collection
     */
    public function getExploreCategoryItemsProperty(): array|Collection
    {
        $exploreCategoryItems = match ($this->exploreCategory->type) {
            ExploreCategoryTypes::MostPopularShows, ExploreCategoryTypes::MostPopularLiteratures => $this->exploreCategory->mostPopularShows($this->genre ?? $this->theme)->exploreCategoryItems,
            ExploreCategoryTypes::UpcomingShows => $this->exploreCategory->upcomingShows($this->genre ?? $this->theme)->exploreCategoryItems,
            ExploreCategoryTypes::NewShows => $this->exploreCategory->newShows($this->genre ?? $this->theme)->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::RecentlyUpdateShows => $this->exploreCategory->recentlyUpdatedShows($this->genre ?? $this->theme)->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::RecentlyFinishedShows => $this->exploreCategory->recentlyFinishedShows($this->genre ?? $this->theme)->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::ContinuingShows => $this->exploreCategory->animeContinuing($this->genre ?? $this->theme)->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::ShowsSeason => $this->exploreCategory->animeSeason($this->genre ?? $this->theme)->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::UpcomingLiteratures => $this->exploreCategory->upcomingLiterature($this->genre ?? $this->theme)->exploreCategoryItems,
            ExploreCategoryTypes::NewLiteratures => $this->exploreCategory->newLiterature($this->genre ?? $this->theme)->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::RecentlyUpdateLiteratures => $this->exploreCategory->recentlyUpdatedLiterature($this->genre ?? $this->theme)->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::RecentlyFinishedLiteratures => $this->exploreCategory->recentlyFinishedLiterature($this->genre ?? $this->theme)->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::ContinuingLiteratures => $this->exploreCategory->literatureContinuing($this->genre ?? $this->theme)->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::LiteraturesSeason => $this->exploreCategory->literatureSeason($this->genre ?? $this->theme)->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::Characters => $this->exploreCategory->charactersBornToday()->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::People => $this->exploreCategory->peopleBornToday()->exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            ExploreCategoryTypes::Songs => $this->exploreCategory->exploreCategoryItems()->limit(10)->get()->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            }),
            default => $this->exploreCategory->exploreCategoryItems()->limit(10)->get()
        };

        if ($this->exploreCategory->type === ExploreCategoryTypes::Shows && $this->exploreCategory->size == ExploreCategorySize::Small) {
            return $exploreCategoryItems->map(function ($exploreCategoryItem) {
                return $exploreCategoryItem->model;
            });
        }

        return $exploreCategoryItems;
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
