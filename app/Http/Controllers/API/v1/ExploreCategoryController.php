<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ExploreCategoryTypes;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetExplorePageRequest;
use App\Http\Resources\ExploreCategoryResource;
use App\Models\Anime;
use App\Models\ExploreCategory;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MediaSong;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;

class ExploreCategoryController extends Controller
{
    /**
     * Returns the necessary data for the Anime explore page.
     *
     * @param GetExplorePageRequest $request
     *
     * @return JsonResponse
     */
    function index(GetExplorePageRequest $request): JsonResponse
    {
        // Get explore categories
        $exploreCategories = ExploreCategory::orderBy('position')
            ->with([
                'exploreCategoryItems.model' => function (MorphTo $morphTo) {
                    $morphTo->limit(10)->constrain([
                        MediaSong::class => function (Builder $query) {
                            $query->with([
                                'song' => function ($query) {
                                    $query->with(['media', 'mediaStat', 'translation'])
                                        ->when(auth()->user(), function ($query, $user) {
                                            $query->with(['mediaRatings' => function ($query) use ($user) {
                                                $query->where([
                                                    ['user_id', '=', $user->id]
                                                ]);
                                            }]);
                                        });
                                },
                                'model' => function ($query) {
                                    $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                        ->when(auth()->user(), function ($query, $user) {
                                            $query->with(['mediaRatings' => function ($query) use ($user) {
                                                $query->where([
                                                    ['user_id', '=', $user->id]
                                                ]);
                                            }, 'library' => function ($query) use ($user) {
                                                $query->where('user_id', '=', $user->id);
                                            }])
                                                ->withExists([
                                                    'favoriters as isFavorited' => function ($query) use ($user) {
                                                        $query->where('user_id', '=', $user->id);
                                                    }
                                                ]);
                                        });
                                }
                            ]);
                        }
                    ]);
                }
            ]);
        $genreOrTheme = null;

        // Check if categories should be genre or theme specific.
        if ($genreID = $request->input('genre_id')) {
            $exploreCategories->where('is_global', true);
            $genreOrTheme = Genre::firstWhere('id', '=', $genreID);
        } else if ($themeID = $request->input('theme_id')) {
            $exploreCategories->where('is_global', true);
            $genreOrTheme = Theme::firstWhere('id', '=', $themeID);
        }

        // Fetch categories
        $exploreCategories = $exploreCategories->get();

        // Loop and set special exploreCategoryItems.model relations
        $exploreCategories = $exploreCategories->map(function (ExploreCategory $exploreCategory) use ($genreOrTheme) {
            return match ($exploreCategory->type) {
                ExploreCategoryTypes::UpNextEpisodes => $exploreCategory->upNextEpisodes(10),
                ExploreCategoryTypes::MostPopularShows => $exploreCategory->mostPopular(Anime::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::UpcomingShows => $exploreCategory->upcoming(Anime::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::NewShows => $exploreCategory->recentlyAdded(Anime::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::RecentlyUpdateShows => $exploreCategory->recentlyUpdated(Anime::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::RecentlyFinishedShows => $exploreCategory->recentlyFinished(Anime::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::ContinuingShows => $exploreCategory->ongoing(Anime::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::ShowsSeason => $exploreCategory->currentSeason(Anime::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::MostPopularLiteratures => $exploreCategory->mostPopular(Manga::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::UpcomingLiteratures => $exploreCategory->upcoming(Manga::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::NewLiteratures => $exploreCategory->recentlyAdded(Manga::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::RecentlyUpdateLiteratures => $exploreCategory->recentlyUpdated(Manga::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::RecentlyFinishedLiteratures => $exploreCategory->recentlyFinished(Manga::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::ContinuingLiteratures => $exploreCategory->ongoing(Manga::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::LiteraturesSeason => $exploreCategory->currentSeason(Manga::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::MostPopularGames => $exploreCategory->mostPopular(Game::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::UpcomingGames => $exploreCategory->upcoming(Game::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::NewGames => $exploreCategory->recentlyAdded(Game::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::RecentlyUpdateGames => $exploreCategory->recentlyUpdated(Game::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::GamesSeason => $exploreCategory->currentSeason(Game::class, $genreOrTheme, 10, false),
                ExploreCategoryTypes::Characters => $exploreCategory->charactersBornToday(10, false),
                ExploreCategoryTypes::People => $exploreCategory->peopleBornToday(10, false),
                ExploreCategoryTypes::ReCAP => $exploreCategory->reCAP(10),
                default => $exploreCategory->loadMissing([
                    'exploreCategoryItems.model' => function (MorphTo $morphTo) {
                        $morphTo->limit(10)->constrain([
                            MediaSong::class => function (Builder $query) {
                                $query->with([
                                    'song' => function ($query) {
                                        $query->with(['media', 'mediaStat']);
                                    },
                                    'model' => function ($query) {
                                        $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                            ->when(auth()->user(), function ($query, $user) {
                                                $query->with(['mediaRatings' => function ($query) use ($user) {
                                                    $query->where([
                                                        ['user_id', '=', $user->id]
                                                    ]);
                                                }, 'library' => function ($query) use ($user) {
                                                    $query->where('user_id', '=', $user->id);
                                                }])
                                                    ->withExists([
                                                        'favoriters as isFavorited' => function ($query) use ($user) {
                                                            $query->where('user_id', '=', $user->id);
                                                        }
                                                    ]);
                                            });
                                    }
                                ]);
                            }
                        ]);
                    }
                ])
            };
        });

        return JSONResult::success([
            'data' => ExploreCategoryResource::collection($exploreCategories)
        ]);
    }

    /**
     * Returns the details of the specified explore category.
     *
     * @param GetExplorePageRequest $request
     * @param ExploreCategory       $exploreCategory
     *
     * @return JsonResponse
     */
    function details(GetExplorePageRequest $request, ExploreCategory $exploreCategory): JsonResponse
    {
        $genreOrTheme = null;

        // Check if categories should be genre or theme specific.
        if ($genreID = $request->input('genre_id')) {
            $genreOrTheme = Genre::firstWhere('id', '=', $genreID);
        } else if ($themeID = $request->input('theme_id')) {
            $genreOrTheme = Theme::firstWhere('id', '=', $themeID);
        }

        $exploreCategory = match ($exploreCategory->type) {
            ExploreCategoryTypes::UpNextEpisodes => $exploreCategory->upNextEpisodes(25),
            ExploreCategoryTypes::MostPopularShows => $exploreCategory->mostPopular(Anime::class, $genreOrTheme, 25),
            ExploreCategoryTypes::UpcomingShows => $exploreCategory->upcoming(Anime::class, $genreOrTheme, 25),
            ExploreCategoryTypes::NewShows => $exploreCategory->recentlyAdded(Anime::class, $genreOrTheme, 25),
            ExploreCategoryTypes::RecentlyUpdateShows => $exploreCategory->recentlyUpdated(Anime::class, $genreOrTheme, 25),
            ExploreCategoryTypes::RecentlyFinishedShows => $exploreCategory->recentlyFinished(Anime::class, $genreOrTheme, 25),
            ExploreCategoryTypes::ContinuingShows => $exploreCategory->ongoing(Anime::class, $genreOrTheme, 25),
            ExploreCategoryTypes::ShowsSeason => $exploreCategory->currentSeason(Anime::class, $genreOrTheme, 25),
            ExploreCategoryTypes::MostPopularLiteratures => $exploreCategory->mostPopular(Manga::class, $genreOrTheme, 25),
            ExploreCategoryTypes::UpcomingLiteratures => $exploreCategory->upcoming(Manga::class, $genreOrTheme, 25),
            ExploreCategoryTypes::NewLiteratures => $exploreCategory->recentlyAdded(Manga::class, $genreOrTheme, 25),
            ExploreCategoryTypes::RecentlyUpdateLiteratures => $exploreCategory->recentlyUpdated(Manga::class, $genreOrTheme, 25),
            ExploreCategoryTypes::RecentlyFinishedLiteratures => $exploreCategory->recentlyFinished(Manga::class, $genreOrTheme, 25),
            ExploreCategoryTypes::ContinuingLiteratures => $exploreCategory->ongoing(Manga::class, $genreOrTheme, 25),
            ExploreCategoryTypes::LiteraturesSeason => $exploreCategory->currentSeason(Manga::class, $genreOrTheme, 25),
            ExploreCategoryTypes::MostPopularGames => $exploreCategory->mostPopular(Game::class, $genreOrTheme, 25),
            ExploreCategoryTypes::UpcomingGames => $exploreCategory->upcoming(Game::class, $genreOrTheme, 25),
            ExploreCategoryTypes::NewGames => $exploreCategory->recentlyAdded(Game::class, $genreOrTheme, 25),
            ExploreCategoryTypes::RecentlyUpdateGames => $exploreCategory->recentlyUpdated(Game::class, $genreOrTheme, 25),
            ExploreCategoryTypes::GamesSeason => $exploreCategory->currentSeason(Game::class, $genreOrTheme, 25),
            ExploreCategoryTypes::Characters => $exploreCategory->charactersBornToday(25),
            ExploreCategoryTypes::People => $exploreCategory->peopleBornToday(25),
            ExploreCategoryTypes::ReCAP => $exploreCategory->reCAP(25),
            default => $exploreCategory->load([
                'exploreCategoryItems.model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Anime::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translation', 'tv_rating', 'themes']);
                        },
                        Game::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translation', 'tv_rating', 'themes']);
                        },
                        Genre::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                        Manga::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translation', 'tv_rating', 'themes']);
                        },
                        MediaSong::class => function (Builder $query) {
                            $query->with([
                                'song' => function ($query) {
                                    $query->with(['media', 'mediaStat']);
                                },
                                'model' => function ($query) {
                                    $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
                                        ->when(auth()->user(), function ($query, $user) {
                                            $query->with(['mediaRatings' => function ($query) use ($user) {
                                                $query->where([
                                                    ['user_id', '=', $user->id]
                                                ]);
                                            }, 'library' => function ($query) use ($user) {
                                                $query->where('user_id', '=', $user->id);
                                            }])
                                                ->withExists([
                                                    'favoriters as isFavorited' => function ($query) use ($user) {
                                                        $query->where('user_id', '=', $user->id);
                                                    }
                                                ]);
                                        });
                                }
                            ]);
                        },
                        Theme::class => function (Builder $query) {
                            $query->with(['media']);
                        }
                    ]);
                }
            ])
        };

        return JSONResult::success([
            'data' => ExploreCategoryResource::collection([$exploreCategory])
        ]);
    }
}
