<?php

namespace App\Models;

use App\Enums\ExploreCategoryTypes;
use App\Scopes\ExploreCategoryIsEnabledScope;
use App\Traits\Model\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\SlugOptions;

class ExploreCategory extends KModel implements Sitemapable, Sortable
{
    use HasSlug,
        SoftDeletes,
        SortableTrait;

    // Table name
    const string TABLE_NAME = 'explore_categories';
    protected $table = self::TABLE_NAME;

    /**
     * The sortable configurations.
     *
     * @var array
     */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new ExploreCategoryIsEnabledScope);
    }

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    /**
     * The query used for sorting.
     *
     * @return Builder
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->withoutGlobalScopes();
    }

    /**
     * The items of the explore category.
     *
     * @return HasMany
     */
    function exploreCategoryItems(): HasMany
    {
        return $this->hasMany(ExploreCategoryItem::class)
            ->orderBy('position');
    }

    /**
     * Returns a base anime query.
     *
     * @param Genre|Theme|null $genreOrTheme
     *
     * @return Anime|Builder
     */
    private function anime(Genre|Theme|null $genreOrTheme): Anime|Builder
    {
        if (is_a($genreOrTheme, Genre::class)) {
            return Anime::whereGenre($genreOrTheme->id);
        } else if (is_a($genreOrTheme, Theme::class)) {
            return Anime::whereTheme($genreOrTheme->id);
        }

        return Anime::query();
    }

    /**
     * Returns a base manga query.
     *
     * @param Genre|Theme|null $genreOrTheme
     *
     * @return Manga|Builder
     */
    private function manga(Genre|Theme|null $genreOrTheme): Manga|Builder
    {
        if (is_a($genreOrTheme, Genre::class)) {
            return Manga::whereGenre($genreOrTheme->id);
        } else if (is_a($genreOrTheme, Theme::class)) {
            return Manga::whereTheme($genreOrTheme->id);
        }

        return Manga::query();
    }

    /**
     * Returns a base game query.
     *
     * @param Genre|Theme|null $genreOrTheme
     *
     * @return Game|Builder
     */
    private function game(Genre|Theme|null $genreOrTheme): Game|Builder
    {
        if (is_a($genreOrTheme, Genre::class)) {
            return Game::whereGenre($genreOrTheme->id);
        } else if (is_a($genreOrTheme, Theme::class)) {
            return Game::whereTheme($genreOrTheme->id);
        }

        return Game::query();
    }

    /**
     * The relations each listed model needs to render.
     *
     * @var array
     */
    public const array ITEM_RELATIONS = [
        Anime::class => ['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'],
        Game::class => ['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'],
        Manga::class => ['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'],
        Genre::class => ['media'],
        Theme::class => ['media'],
        MediaSong::class => ['song.media', 'model.translation'],
    ];

    /**
     * Returns the models shown in each category.
     *
     * @param Collection       $exploreCategories
     * @param Genre|Theme|null $genreOrTheme
     *
     * @return Collection
     */
    public static function itemsFor(Collection $exploreCategories, Genre|Theme|null $genreOrTheme = null): Collection
    {
        $itemsByCategory = $exploreCategories->mapWithKeys(function (ExploreCategory $exploreCategory) use ($genreOrTheme) {
            return [$exploreCategory->id => $exploreCategory->items($genreOrTheme, false)];
        });

        $user = auth()->user();

        $itemsByCategory->flatten()
            ->groupBy(fn ($model) => $model::class)
            ->each(function (Collection $models, string $class) use ($user) {
                $relations = static::ITEM_RELATIONS[$class] ?? null;

                if ($relations === null) {
                    return;
                }

                if ($user !== null && in_array($class, [Anime::class, Game::class, Manga::class], true)) {
                    $relations['library'] = function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    };
                }

                EloquentCollection::make($models->all())->loadMissing($relations);
            });

        return $itemsByCategory;
    }

    /**
     * Returns the models shown in the category.
     *
     * @param Genre|Theme|null $genreOrTheme
     * @param bool             $withRelations
     *
     * @return Collection
     */
    public function items(Genre|Theme|null $genreOrTheme = null, bool $withRelations = true): Collection
    {
        $exploreCategory = match ($this->type) {
            ExploreCategoryTypes::MostPopularShows => $this->mostPopular(Anime::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::UpcomingShows => $this->upcoming(Anime::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::NewShows => $this->recentlyAdded(Anime::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::RecentlyUpdateShows => $this->recentlyUpdated(Anime::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::RecentlyFinishedShows => $this->recentlyFinished(Anime::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::ContinuingShows => $this->ongoing(Anime::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::ShowsSeason => $this->currentSeason(Anime::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::MostPopularLiteratures => $this->mostPopular(Manga::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::UpcomingLiteratures => $this->upcoming(Manga::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::NewLiteratures => $this->recentlyAdded(Manga::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::RecentlyUpdateLiteratures => $this->recentlyUpdated(Manga::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::RecentlyFinishedLiteratures => $this->recentlyFinished(Manga::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::ContinuingLiteratures => $this->ongoing(Manga::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::LiteraturesSeason => $this->currentSeason(Manga::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::MostPopularGames => $this->mostPopular(Game::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::UpcomingGames => $this->upcoming(Game::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::NewGames => $this->recentlyAdded(Game::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::RecentlyUpdateGames => $this->recentlyUpdated(Game::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::GamesSeason => $this->currentSeason(Game::class, $genreOrTheme, 10, $withRelations),
            ExploreCategoryTypes::Characters => $this->charactersBornToday(),
            ExploreCategoryTypes::UpNextEpisodes => $this->upNextEpisodes(),
            ExploreCategoryTypes::People => $this->peopleBornToday(),
            ExploreCategoryTypes::ReCAP => $this->reCAP(),
            default => !$withRelations ? $this->load(array_filter([
                'exploreCategoryItems.model',
                $this->type === ExploreCategoryTypes::Songs ? 'exploreCategoryItems.model.model' : null,
            ])) : $this->load([
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
                        }
                    ]);
                }
            ])
        };

        if ($this->type === ExploreCategoryTypes::Songs) {
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
     * Returns the current most popular anime.
     *
     * @param string|null      $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int              $limit
     * @param bool             $withRelations
     *
     * @return ExploreCategory
     */
    public function mostPopular(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = match ($class) {
            Anime::class => $this->anime($genreOrTheme)
                ->mostPopular($limit, 3, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            Game::class => $this->game($genreOrTheme)
                ->mostPopular($limit, 3, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            Manga::class => $this->manga($genreOrTheme)
                ->mostPopular($limit, 3, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            // No default, so it errors out, and we can fix it.
        };

        $this->next_page_url = $models->nextPageUrl();
        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model
            ]);
        });

        return $this;
    }

    /**
     * Returns the upcoming models.
     *
     * @param string|null      $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int              $limit
     * @param bool             $withRelations
     *
     * @return ExploreCategory
     */
    public function upcoming(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = match ($class) {
            Anime::class => $this->anime($genreOrTheme)
                ->upcoming($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['translation', 'media']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }])
                            ->withExists([
                                'reminderers as isReminded' => function ($query) use ($user) {
                                    $query->where('user_id', '=', $user->id);
                                },
                            ]);
                    }
                })
                ->cursorPaginate($limit),
            Game::class => $this->game($genreOrTheme)
                ->upcoming($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['translation', 'media']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }])
                            ->withExists([
                                'reminderers as isReminded' => function ($query) use ($user) {
                                    $query->where('user_id', '=', $user->id);
                                },
                            ]);
                    }
                })
                ->cursorPaginate($limit),
            Manga::class => $this->manga($genreOrTheme)
                ->upcoming($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['translation', 'media']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }])
                            ->withExists([
                                'reminderers as isReminded' => function ($query) use ($user) {
                                    $query->where('user_id', '=', $user->id);
                                },
                            ]);
                    }
                })
                ->cursorPaginate($limit),
            // No default, so it errors out, and we can fix it.
        };

        $this->next_page_url = $models->nextPageUrl();
        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model
            ]);
        });

        return $this;
    }

    /**
     * Returns models that have been added recently.
     *
     * @param string|null      $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int              $limit
     * @param bool             $withRelations
     *
     * @return ExploreCategory
     */
    public function recentlyAdded(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = match ($class) {
            Anime::class => $this->anime($genreOrTheme)
                ->recentlyAdded($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            Game::class => $this->game($genreOrTheme)
                ->recentlyAdded($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            Manga::class => $this->manga($genreOrTheme)
                ->recentlyAdded($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
        };

        $this->next_page_url = $models->nextPageUrl();
        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model
            ]);
        });

        return $this;
    }

    /**
     * Returns models that have been updated recently.
     *
     * @param string|null      $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int              $limit
     * @param bool             $withRelations
     *
     * @return ExploreCategory
     */
    public function recentlyUpdated(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = match ($class) {
            Anime::class => $this->anime($genreOrTheme)
                ->recentlyUpdated($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            Game::class => $this->game($genreOrTheme)
                ->recentlyUpdated($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            Manga::class => $this->manga($genreOrTheme)
                ->recentlyUpdated($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
        };

        $this->next_page_url = $models->nextPageUrl();
        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model
            ]);
        });

        return $this;
    }

    /**
     * Returns models that have finished recently.
     *
     * @param string|null      $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int              $limit
     * @param bool             $withRelations
     *
     * @return ExploreCategory
     */
    public function recentlyFinished(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = match ($class) {
            Anime::class => $this->anime($genreOrTheme)
                ->recentlyFinished($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            Manga::class => $this->manga($genreOrTheme)
                ->recentlyFinished($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
        };

        $this->next_page_url = $models->nextPageUrl();
        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model
            ]);
        });

        return $this;
    }

    /**
     * Append the models continuing since past season(s) to the category's items.
     *
     * @param string|null      $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int              $limit
     * @param bool             $withRelations
     *
     * @return ExploreCategory
     */
    public function ongoing(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = match ($class) {
            Anime::class => $this->anime($genreOrTheme)
                ->ongoing($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            Manga::class => $this->manga($genreOrTheme)
                ->ongoing($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
        };

        $this->next_page_url = $models->nextPageUrl();
        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model
            ]);
        });

        return $this;
    }

    /**
     * Append the models of the current season to the category's items.
     *
     * @param string|null      $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int              $limit
     * @param bool             $withRelations
     *
     * @return ExploreCategory
     */
    public function currentSeason(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = match ($class) {
            Anime::class => $this->anime($genreOrTheme)
                ->currentSeason($limit, (bool) $genreOrTheme?->is_nsfw)
                ->where('air_day', '=', today()->dayOfWeek)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            Game::class => $this->game($genreOrTheme)
                ->currentSeason($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
            Manga::class => $this->manga($genreOrTheme)
                ->currentSeason($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating']);

                    if ($user = auth()->user()) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    }
                })
                ->cursorPaginate($limit),
        };

        $this->next_page_url = $models->nextPageUrl();
        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model
            ]);
        });

        return $this;
    }

    /**
     * Append the characters born today to the category's items.
     *
     * @param int  $limit
     * @param bool $withRelations
     *
     * @return ExploreCategory
     */
    public function charactersBornToday(int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = Character::bornToday($limit)
            ->with($withRelations ? ['media', 'translation'] : [])
            ->cursorPaginate($limit);

        $this->next_page_url = $models->nextPageUrl();
        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model,
            ]);
        });

        return $this;
    }

    /**
     * Append the people born today to the category's items
     *
     * @param int  $limit
     * @param bool $withRelations
     *
     * @return ExploreCategory
     */
    public function peopleBornToday(int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = Person::bornToday($limit)
            ->with($withRelations ? ['media'] : [])
            ->cursorPaginate($limit);

        $this->next_page_url = $models->nextPageUrl();
        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model,
            ]);
        });

        return $this;
    }

    /**
     * Append the user's Re:CAP entries to the category's items
     *
     * @param int $limit
     *
     * @return ExploreCategory
     */
    public function reCAP(int $limit = 10): ExploreCategory
    {
        $models = auth()->user()?->recaps()
            ->selectRaw('MIN(id) as id, year, MAX(month) as month')
            ->orderBy('year', 'desc')
            ->groupBy('year')
            ->cursorPaginate($limit);

        $this->next_page_url = $models?->nextPageUrl();
        $this->exploreCategoryItems = $models?->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model,
            ]);
        }) ?? collect();

        return $this;
    }

    /**
     * Append the user's up-next episodes entries to the category's items
     *
     * @param int $limit
     *
     * @return ExploreCategory
     */
    public function upNextEpisodes(int $limit = 10): ExploreCategory
    {
        $models = auth()->user()?->up_next_episodes()
            ->cursorPaginate($limit);

        $this->next_page_url = $models?->nextPageUrl();
        $this->exploreCategoryItems = $models?->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model,
            ]);
        }) ?? collect();

        return $this;
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('explore.details', $this))
            ->setChangeFrequency('daily')
            ->setLastModificationDate($this->updated_at);
    }
}
