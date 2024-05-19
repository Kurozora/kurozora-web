<?php

namespace App\Livewire\Components;

use App\Enums\ExploreCategoryTypes;
use App\Models\Anime;
use App\Models\ExploreCategory;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MediaSong;
use App\Models\Theme;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
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
     * Determines whether to load the page.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param ExploreCategory $exploreCategory
     * @param Genre|null $genre
     * @param Theme|null $theme
     * @return void
     */
    public function mount(ExploreCategory $exploreCategory, ?Genre $genre, ?Theme $theme): void
    {
        $this->exploreCategory = $exploreCategory;

        // Cause Livewire inits a new Model when null.
        // Can't have fucking optionals in Detroit... ffs...
        if (!empty($genre->id)) {
            $this->genre = $genre;
        }

        if (!empty($theme->id)) {
            $this->theme = $theme;
        }
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * The array containing the explore category item data.
     *
     * @return Collection
     */
    public function getExploreCategoryItemsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $exploreCategory = match ($this->exploreCategory->type) {
            ExploreCategoryTypes::MostPopularShows => $this->exploreCategory->mostPopular(Anime::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::UpcomingShows => $this->exploreCategory->upcoming(Anime::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::NewShows => $this->exploreCategory->recentlyAdded(Anime::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::RecentlyUpdateShows => $this->exploreCategory->recentlyUpdated(Anime::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::RecentlyFinishedShows => $this->exploreCategory->recentlyFinished(Anime::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::ContinuingShows => $this->exploreCategory->ongoing(Anime::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::ShowsSeason => $this->exploreCategory->currentSeason(Anime::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::MostPopularLiteratures => $this->exploreCategory->mostPopular(Manga::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::UpcomingLiteratures => $this->exploreCategory->upcoming(Manga::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::NewLiteratures => $this->exploreCategory->recentlyAdded(Manga::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::RecentlyUpdateLiteratures => $this->exploreCategory->recentlyUpdated(Manga::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::RecentlyFinishedLiteratures => $this->exploreCategory->recentlyFinished(Manga::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::ContinuingLiteratures => $this->exploreCategory->ongoing(Manga::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::LiteraturesSeason => $this->exploreCategory->currentSeason(Manga::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::MostPopularGames => $this->exploreCategory->mostPopular(Game::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::UpcomingGames => $this->exploreCategory->upcoming(Game::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::NewGames => $this->exploreCategory->recentlyAdded(Game::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::RecentlyUpdateGames => $this->exploreCategory->recentlyUpdated(Game::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::GamesSeason => $this->exploreCategory->currentSeason(Game::class, $this->genre ?? $this->theme),
            ExploreCategoryTypes::Characters => $this->exploreCategory->charactersBornToday(),
            ExploreCategoryTypes::People => $this->exploreCategory->peopleBornToday(),
            ExploreCategoryTypes::ReCAP => $this->exploreCategory->reCAP(),
            default => $this->exploreCategory->load([
                'exploreCategoryItems.model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Anime::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translations', 'tv_rating', 'themes'])
                                ->when(auth()->user(), function ($query, $user) {
                                    return $query->with(['library' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
                        },
                        Game::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translations', 'tv_rating', 'themes'])
                                ->when(auth()->user(), function ($query, $user) {
                                    return $query->with(['library' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
                        },
                        Genre::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                        Manga::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translations', 'tv_rating', 'themes'])
                                ->when(auth()->user(), function ($query, $user) {
                                    return $query->with(['library' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
                        },
                        MediaSong::class => function (Builder $query) {
                            $query->with(['song.media', 'model.translations']);
                        },
                        Theme::class => function (Builder $query) {
                            $query->with(['media']);
                        }
                    ]);
                }
            ])
        };

        if ($this->exploreCategory->type === ExploreCategoryTypes::Songs) {
            return $exploreCategory->exploreCategoryItems->map(function ($exploreCategoryItem) {
                if ($exploreCategoryItem?->model->model != null) {
                    return $exploreCategoryItem->model;
                }
                return null;
            })->filter();
        }

        return $exploreCategory->exploreCategoryItems->map(function ($exploreCategoryItem) {
            return $exploreCategoryItem?->model;
        })->filter();
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
