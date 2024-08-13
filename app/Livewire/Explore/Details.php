<?php

namespace App\Livewire\Explore;

use App\Enums\ExploreCategoryTypes;
use App\Models\Anime;
use App\Models\ExploreCategory;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MediaSong;
use App\Models\Theme;
use App\Traits\Livewire\WithPagination;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
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
     * Determines whether to load the page.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * The array containing the explore category items data.
     *
     * @return Collection exploreCategoryItems
     */
    public function getExploreCategoryItemsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $exploreCategory = match ($this->exploreCategory->type) {
            ExploreCategoryTypes::UpNextEpisodes => $this->exploreCategory->upNextEpisodes(25),
            ExploreCategoryTypes::MostPopularShows => $this->exploreCategory->mostPopular(Anime::class, null, 25),
            ExploreCategoryTypes::UpcomingShows => $this->exploreCategory->upcoming(Anime::class, null, 25),
            ExploreCategoryTypes::NewShows => $this->exploreCategory->recentlyAdded(Anime::class, null, 25),
            ExploreCategoryTypes::RecentlyUpdateShows => $this->exploreCategory->recentlyUpdated(Anime::class, null, 25),
            ExploreCategoryTypes::RecentlyFinishedShows => $this->exploreCategory->recentlyFinished(Anime::class, null, 25),
            ExploreCategoryTypes::ContinuingShows => $this->exploreCategory->ongoing(Anime::class, null, 25),
            ExploreCategoryTypes::ShowsSeason => $this->exploreCategory->currentSeason(Anime::class, null, 25),
            ExploreCategoryTypes::MostPopularLiteratures => $this->exploreCategory->mostPopular(Manga::class, null, 25),
            ExploreCategoryTypes::UpcomingLiteratures => $this->exploreCategory->upcoming(Manga::class, null, 25),
            ExploreCategoryTypes::NewLiteratures => $this->exploreCategory->recentlyAdded(Manga::class, null, 25),
            ExploreCategoryTypes::RecentlyUpdateLiteratures => $this->exploreCategory->recentlyUpdated(Manga::class, null, 25),
            ExploreCategoryTypes::RecentlyFinishedLiteratures => $this->exploreCategory->recentlyFinished(Manga::class, null, 25),
            ExploreCategoryTypes::ContinuingLiteratures => $this->exploreCategory->ongoing(Manga::class, null, 25),
            ExploreCategoryTypes::LiteraturesSeason => $this->exploreCategory->currentSeason(Manga::class, null, 25),
            ExploreCategoryTypes::MostPopularGames => $this->exploreCategory->mostPopular(Game::class, null, 25),
            ExploreCategoryTypes::UpcomingGames => $this->exploreCategory->upcoming(Game::class, null, 25),
            ExploreCategoryTypes::NewGames => $this->exploreCategory->recentlyAdded(Game::class, null, 25),
            ExploreCategoryTypes::RecentlyUpdateGames => $this->exploreCategory->recentlyUpdated(Game::class, null, 25),
            ExploreCategoryTypes::GamesSeason => $this->exploreCategory->currentSeason(Game::class, null, 25),
            ExploreCategoryTypes::Characters => $this->exploreCategory->charactersBornToday(25),
            ExploreCategoryTypes::People => $this->exploreCategory->peopleBornToday(25),
            ExploreCategoryTypes::ReCAP => $this->exploreCategory->reCAP(25),
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

        return $exploreCategory->exploreCategoryItems->map(function ($exploreCategoryItem) {
            return $exploreCategoryItem->model;
        })->filter();
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
