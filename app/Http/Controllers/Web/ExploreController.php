<?php

namespace App\Http\Controllers\Web;

use App\Enums\ExploreCategoryTypes;
use App\Http\Controllers\Controller;
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

class ExploreController extends Controller
{
    /**
     * Show an explore category's page.
     *
     * @param ExploreCategory $exploreCategory
     *
     * @return Application|Factory|View
     */
    public function show(ExploreCategory $exploreCategory): Application|Factory|View
    {
        return view('explore.details', [
            'exploreCategory' => $exploreCategory,
            'items' => $this->items($exploreCategory),
        ]);
    }

    /**
     * The models an explore category presents.
     *
     * @param ExploreCategory $exploreCategory
     *
     * @return Collection
     */
    protected function items(ExploreCategory $exploreCategory): Collection
    {
        $exploreCategory = match ($exploreCategory->type) {
            ExploreCategoryTypes::UpNextEpisodes => $exploreCategory->upNextEpisodes(10),
            ExploreCategoryTypes::MostPopularShows => $exploreCategory->mostPopular(Anime::class, null, 25),
            ExploreCategoryTypes::UpcomingShows => $exploreCategory->upcoming(Anime::class, null, 25),
            ExploreCategoryTypes::NewShows => $exploreCategory->recentlyAdded(Anime::class, null, 25),
            ExploreCategoryTypes::RecentlyUpdateShows => $exploreCategory->recentlyUpdated(Anime::class, null, 25),
            ExploreCategoryTypes::RecentlyFinishedShows => $exploreCategory->recentlyFinished(Anime::class, null, 25),
            ExploreCategoryTypes::ContinuingShows => $exploreCategory->ongoing(Anime::class, null, 25),
            ExploreCategoryTypes::ShowsSeason => $exploreCategory->currentSeason(Anime::class, null, 25),
            ExploreCategoryTypes::MostPopularLiteratures => $exploreCategory->mostPopular(Manga::class, null, 25),
            ExploreCategoryTypes::UpcomingLiteratures => $exploreCategory->upcoming(Manga::class, null, 25),
            ExploreCategoryTypes::NewLiteratures => $exploreCategory->recentlyAdded(Manga::class, null, 25),
            ExploreCategoryTypes::RecentlyUpdateLiteratures => $exploreCategory->recentlyUpdated(Manga::class, null, 25),
            ExploreCategoryTypes::RecentlyFinishedLiteratures => $exploreCategory->recentlyFinished(Manga::class, null, 25),
            ExploreCategoryTypes::ContinuingLiteratures => $exploreCategory->ongoing(Manga::class, null, 25),
            ExploreCategoryTypes::LiteraturesSeason => $exploreCategory->currentSeason(Manga::class, null, 25),
            ExploreCategoryTypes::MostPopularGames => $exploreCategory->mostPopular(Game::class, null, 25),
            ExploreCategoryTypes::UpcomingGames => $exploreCategory->upcoming(Game::class, null, 25),
            ExploreCategoryTypes::NewGames => $exploreCategory->recentlyAdded(Game::class, null, 25),
            ExploreCategoryTypes::RecentlyUpdateGames => $exploreCategory->recentlyUpdated(Game::class, null, 25),
            ExploreCategoryTypes::GamesSeason => $exploreCategory->currentSeason(Game::class, null, 25),
            ExploreCategoryTypes::Characters => $exploreCategory->charactersBornToday(25),
            ExploreCategoryTypes::People => $exploreCategory->peopleBornToday(25),
            ExploreCategoryTypes::ReCAP => $exploreCategory->reCAP(25),
            default => $exploreCategory->load([
                'exploreCategoryItems.model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Anime::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translation', 'tvRating', 'themes'])
                                ->when(auth()->user(), function ($query, $user) {
                                    return $query->with(['library' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
                        },
                        Game::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translation', 'tvRating', 'themes'])
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
                            $query->with(['genres', 'mediaStat', 'media', 'translation', 'tvRating', 'themes'])
                                ->when(auth()->user(), function ($query, $user) {
                                    return $query->with(['library' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
                        },
                        MediaSong::class => function (Builder $query) {
                            $query->with(['song.media', 'model.translation']);
                        },
                        Theme::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                    ]);
                },
            ]),
        };

        return $exploreCategory->exploreCategoryItems
            ->map(fn ($exploreCategoryItem) => $exploreCategoryItem->model)
            ->filter();
    }
}
