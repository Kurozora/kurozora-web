<?php

namespace App\Models;

use App\Scopes\ExploreCategoryIsEnabledScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Request;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ExploreCategory extends KModel implements Sitemapable, Sortable
{
    use HasSlug,
        SoftDeletes,
        SortableTrait;

    // Table name
    const TABLE_NAME = 'explore_categories';
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

        if (app('explore.only_enabled')) {
            static::addGlobalScope(new ExploreCategoryIsEnabledScope);
        }
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        if (Request::wantsJson()) {
            return parent::getRouteKeyName();
        }
        return 'slug';
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
     * Returns the current most popular anime.
     *
     * @param string|null $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int $limit
     * @param bool $withRelations
     * @return ExploreCategory
     */
    public function mostPopular(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        // Find location of cached data
//        $cacheKey = self::cacheKey(['name' => 'mostPopular', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'class' => $class, 'modelType' => $genreOrTheme?->getMorphClass(), 'modelID' => $genreOrTheme?->id, 'limit' => $limit, 'withRelations' => $withRelations]);

        // Retrieve or save cached result
//        return Cache::remember($cacheKey, 60*60*12, function () use ($class, $genreOrTheme, $limit, $withRelations) {
            $models = match ($class) {
                Anime::class => $this->anime($genreOrTheme)
                    ->mostPopular($limit, 3, (bool) $genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                    ->get(),
                Game::class => $this->game($genreOrTheme)
                    ->mostPopular($limit, 3, (bool)$genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                    ->get(),
                Manga::class => $this->manga($genreOrTheme)
                    ->mostPopular($limit, 3, (bool)$genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                    ->get(),
                // No default, so it errors out, and we can fix it.
            };

            $this->exploreCategoryItems = $models->map(function ($model) {
                return new ExploreCategoryItem([
                    'model' => $model
                ]);
            });

            return $this;
//        });
    }

    /**
     * Returns the upcoming models.
     *
     * @param string|null $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int $limit
     * @param bool $withRelations
     * @return ExploreCategory
     */
    public function upcoming(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
//        $cacheKey = self::cacheKey(['name' => 'upcoming', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'class' => $class, 'modelType' => $genreOrTheme?->getMorphClass(), 'modelID' => $genreOrTheme?->id, 'limit' => $limit, 'withRelations' => $withRelations]);

        // Retrieve or save cached result
//        return Cache::remember($cacheKey, 60*60*12, function () use ($class, $genreOrTheme, $limit, $withRelations) {
            $models = match ($class) {
                Anime::class => $this->anime($genreOrTheme)
                    ->upcoming($limit, (bool)$genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['translations', 'media']);

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
                    ->get(),
                Game::class => $this->game($genreOrTheme)
                    ->upcoming($limit, (bool)$genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['translations', 'media']);

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
                    ->get(),
                Manga::class => $this->manga($genreOrTheme)
                    ->upcoming($limit, (bool)$genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['translations', 'media']);

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
                    ->get(),
                // No default, so it errors out, and we can fix it.
            };

            $this->exploreCategoryItems = $models->map(function ($model) {
                return new ExploreCategoryItem([
                    'model' => $model
                ]);
            });

            return $this;
//        });
    }

    /**
     * Returns models that's been added recently.
     *
     * @param string|null $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int $limit
     * @param bool $withRelations
     * @return ExploreCategory
     */
    public function recentlyAdded(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = match ($class) {
            Anime::class => $this->anime($genreOrTheme)
                ->recentlyAdded($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                ->get(),
            Game::class => $this->game($genreOrTheme)
                ->recentlyAdded($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                ->get(),
            Manga::class => $this->manga($genreOrTheme)
                ->recentlyAdded($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                ->get(),
        };

        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model
            ]);
        });

        return $this;
    }

    /**
     * Returns anime that's been added recently.
     *
     * @param string|null $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int $limit
     * @param bool $withRelations
     * @return ExploreCategory
     */
    public function recentlyUpdated(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = match ($class) {
            Anime::class => $this->anime($genreOrTheme)
                ->recentlyUpdated($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                ->get(),
            Game::class => $this->game($genreOrTheme)
                ->recentlyUpdated($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                ->get(),
            Manga::class => $this->manga($genreOrTheme)
                ->recentlyUpdated($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                ->get(),
        };

        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model
            ]);
        });

        return $this;
    }

    /**
     * Returns anime that's finished recently.
     *
     * @param string|null $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int $limit
     * @param bool $withRelations
     * @return ExploreCategory
     */
    public function recentlyFinished(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = match ($class) {
            Anime::class => $this->anime($genreOrTheme)
                ->recentlyFinished($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                ->get(),
            Manga::class => $this->manga($genreOrTheme)
                ->recentlyFinished($limit, (bool) $genreOrTheme?->is_nsfw)
                ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                ->get(),
        };

        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model
            ]);
        });

        return $this;
    }

    /**
     * Append the shows continuing since past season(s) to the category's items.
     *
     * @param string|null $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int $limit
     * @param bool $withRelations
     * @return ExploreCategory
     */
    public function ongoing(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        // Find location of cached data
//        $cacheKey = self::cacheKey(['name' => 'ongoing', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'class' => $class, 'modelType' => $genreOrTheme?->getMorphClass(), 'modelID' => $genreOrTheme?->id, 'withRelations' => $withRelations]);

        // Retrieve or save cached result
//        return Cache::remember($cacheKey, 60*60*12, function () use ($class, $genreOrTheme, $limit, $withRelations) {
            $models = match ($class) {
                Anime::class => $this->anime($genreOrTheme)
                    ->ongoing($limit, (bool) $genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                    ->get(),
                Manga::class => $this->manga($genreOrTheme)
                    ->ongoing($limit, (bool) $genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                    ->get(),
            };

            $this->exploreCategoryItems = $models->map(function ($model) {
                return new ExploreCategoryItem([
                    'model' => $model
                ]);
            });

            return $this;
//        });
    }

    /**
     * Append the models of the current season to the category's items.
     *
     * @param string|null $class
     * @param Genre|Theme|null $genreOrTheme
     * @param int $limit
     * @param bool $withRelations
     * @return ExploreCategory
     */
    public function currentSeason(string|null $class = null, Genre|Theme|null $genreOrTheme = null, int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        // Find location of cached data
//        $cacheKey = self::cacheKey(['name' => 'currentSeason', 'id' => $this->id, 'tvRating' => self::getTvRatingSettings(), 'class' => $class, 'modelType' => $genreOrTheme?->getMorphClass(), 'modelID' => $genreOrTheme?->id, 'withRelations' => $withRelations]);

        // Retrieve or save cached result
//        return Cache::remember($cacheKey, 60*60*12, function () use ($class, $genreOrTheme, $limit, $withRelations) {
            $animeSeason = match ($class) {
                Anime::class => $this->anime($genreOrTheme)
                    ->currentSeason($limit, (bool) $genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                    ->get(),
                Game::class => $this->game($genreOrTheme)
                    ->currentSeason($limit, (bool) $genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                    ->get(),
                Manga::class => $this->manga($genreOrTheme)
                    ->currentSeason($limit, (bool) $genreOrTheme?->is_nsfw)
                    ->when($withRelations, function ($query) {
                        $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);

                        if ($user = auth()->user()) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        }
                    })
                    ->get(),
            };

            $this->exploreCategoryItems = $animeSeason->map(function (Anime $anime) {
                return new ExploreCategoryItem([
                    'model' => $anime
                ]);
            });

            return $this;
//        });
    }

    /**
     * Append the characters born today to the category's items.
     *
     * @param int $limit
     * @param bool $withRelations
     * @return ExploreCategory
     */
    public function charactersBornToday(int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = Character::bornToday($limit)
            ->with($withRelations ? ['media', 'translations'] : [])
            ->get();

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
     * @param int $limit
     * @param bool $withRelations
     * @return ExploreCategory
     */
    public function peopleBornToday(int $limit = 10, bool $withRelations = true): ExploreCategory
    {
        $models = Person::bornToday($limit)
            ->with($withRelations ? ['media'] : [])
            ->get();

        $this->exploreCategoryItems = $models->map(function ($model) {
            return new ExploreCategoryItem([
                'model' => $model,
            ]);
        });

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
            ->setChangeFrequency('weekly');
    }
}
