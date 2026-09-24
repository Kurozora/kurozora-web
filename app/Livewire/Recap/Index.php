<?php

namespace App\Livewire\Recap;

use App\Enums\MediaCollection;
use App\Enums\RecapStatType;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MediaStaff;
use App\Models\Person;
use App\Models\Recap;
use App\Models\RecapItem;
use App\Models\RecapStat;
use App\Models\Studio;
use App\Models\Theme;
use App\Models\UserFavorite;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    /**
     * The selected year.
     *
     * @var int|string|null $year
     */
    public int|string|null $year = null;

    /**
     * The selected month.
     *
     * @var ?int $month
     */
    public ?int $month = null;

    /**
     * Determines whether to load the page.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = true;

    /**
     * Determines whether to loading screen is shown.
     *
     * @var bool $loadingScreenEnabled
     */
    public bool $loadingScreenEnabled = true;

    /**
     * Whether the prompt to allow canvas access is shown.
     *
     * @var bool $confirmingCanvasAccess
     */
    public bool $confirmingCanvasAccess = false;

    /**
     * The query strings of the component.
     *
     * @return array
     */
    protected function queryString(): array
    {
        return [
            'year' => ['except' => now()->year],
        ];
    }

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        if (empty($this->year) || !ctype_digit((string) $this->year)) {
            $this->year = now()->year;
        }

        $this->year = (int) $this->year;
        $this->month = $this->defaultMonth();
    }

    /**
     * Resets the selected month when the year changes.
     *
     * @return void
     */
    public function updatedYear(): void
    {
        $this->year = (int) $this->year;
        unset($this->recapPeriods);
        $this->month = $this->defaultMonth();
    }

    /**
     * The month selected by default for the selected year.
     *
     * @return int
     */
    protected function defaultMonth(): int
    {
        $months = $this->recapPeriods->pluck('month');

        if ($this->year === now()->year) {
            $previousMonth = now()->subMonth()->month;

            if (now()->month !== 1 && $months->contains($previousMonth)) {
                return $previousMonth;
            }

            return $months->reject(fn ($month) => $month === 0)->max() ?? now()->month;
        }

        if ($months->contains(0)) {
            return 0;
        }

        return $months->max() ?? 0;
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
     * Get the user's recap data.
     *
     * @return Collection|LengthAwarePaginator
     */
    #[Computed]
    public function recaps(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $recaps = auth()->user()->recaps()
            ->with([
                'recapItems.role',
                'recapItems.model' => function (MorphTo $morphTo) {
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
                        Manga::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translation', 'tvRating', 'themes'])
                                ->when(auth()->user(), function ($query, $user) {
                                    return $query->with(['library' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
                        },
                        Studio::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                        Character::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                        Person::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                    ]);
                }
            ])
            ->where('year', '=', $this->year)
            ->where('month', '=', $this->month)
            ->get();

        $this->loadingScreenEnabled = false;

        return $recaps;
    }

    /**
     * Get the user's recap years.
     *
     * @return Collection
     */
    #[Computed]
    public function recapYears(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $recapYears = auth()->user()->recaps()
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->get();

        $this->loadingScreenEnabled = false;

        return $recapYears;
    }

    /**
     * Get the user's recap periods of the selected year.
     *
     * @return Collection
     */
    #[Computed]
    public function recapPeriods(): Collection
    {
        return auth()->user()->recaps()
            ->select('month', 'year')
            ->distinct()
            ->where('year', '=', $this->year)
            ->orderBy('month')
            ->get();
    }

    /**
     * Whether the user has a yearly recap for the selected year.
     *
     * @return bool
     */
    public function getHasYearlyRecapProperty(): bool
    {
        return $this->recapPeriods->contains('month', '=', 0);
    }

    /**
     * Get the user's recap months.
     *
     * @return Collection
     */
    public function getRecapMonthsProperty(): Collection
    {
        $recapMonths = $this->recapPeriods
            ->where('month', '!=', 0);

        if (now()->year === $this->year && !$recapMonths->contains('month', '=', now()->month)) {
            $recapMonths->push(Recap::make([
                'year' => now()->year,
                'month' => now()->month,
            ]));
        }

        return $recapMonths->sortBy('month')
            ->values();
    }

    /**
     * The user's top titles of the selected year paired with those of the year before.
     *
     * @return Collection
     */
    public function getRecapComparisonsProperty(): Collection
    {
        if ($this->month !== 0) {
            return collect();
        }

        $types = [Anime::class, Manga::class, Game::class];
        $previousRecaps = auth()->user()->recaps()
            ->with([
                'recapItems' => function (HasMany $query) {
                    $query->where('position', '=', 1)
                        ->with([
                            'model' => function (MorphTo $morphTo) {
                                $morphTo->constrain([
                                    Anime::class => function (Builder $query) {
                                        $query->with(['media', 'translation']);
                                    },
                                    Game::class => function (Builder $query) {
                                        $query->with(['media', 'translation']);
                                    },
                                    Manga::class => function (Builder $query) {
                                        $query->with(['media', 'translation']);
                                    },
                                ]);
                            }
                        ]);
                }
            ])
            ->where('year', '=', $this->year - 1)
            ->where('month', '=', 0)
            ->whereIn('type', $types)
            ->get()
            ->keyBy('type');

        return $this->recaps
            ->whereIn('type', $types)
            ->map(function (Recap $recap) use ($previousRecaps) {
                $currentRecapItem = $recap->recapItems->first();
                $previousRecapItem = $previousRecaps->get($recap->type)?->recapItems->first();

                if ($currentRecapItem?->model === null || $previousRecapItem?->model === null) {
                    return null;
                }

                return [
                    'recap' => $recap,
                    'title' => match ($recap->type) {
                        Manga::class => __('Top Manga'),
                        Game::class => __('Top Game'),
                        default => __('Top Anime'),
                    },
                    'subtitle' => __('Comparison'),
                    'currentModel' => $currentRecapItem->model,
                    'currentDetail' => $this->recapItemDetail($currentRecapItem),
                    'previousModel' => $previousRecapItem->model,
                    'previousDetail' => $this->recapItemDetail($previousRecapItem),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * The IDs of the recap titles the user favorited keyed by type.
     *
     * @return array
     */
    #[Computed]
    public function favoritedModelIDs(): array
    {
        $recapItems = $this->recaps
            ->whereIn('type', [Anime::class, Manga::class, Game::class])
            ->pluck('recapItems')
            ->flatten();

        if ($recapItems->isEmpty()) {
            return [];
        }

        return UserFavorite::where('user_id', '=', auth()->id())
            ->whereIn('favorable_type', $recapItems->pluck('model_type')->unique())
            ->whereIn('favorable_id', $recapItems->pluck('model_id')->unique())
            ->get(['favorable_type', 'favorable_id'])
            ->groupBy('favorable_type')
            ->map(fn ($userFavorites) => $userFavorites->pluck('favorable_id')->all())
            ->all();
    }

    /**
     * The measures of each recap's titles keyed by recap and title.
     *
     * @return array
     */
    #[Computed]
    public function recapItemDetails(): array
    {
        return $this->recaps
            ->mapWithKeys(function (Recap $recap) {
                return [
                    $recap->id => $recap->recapItems
                        ->mapWithKeys(function (RecapItem $recapItem) use ($recap) {
                            return [$recapItem->model_id => $this->recapItemDetail($recapItem, $recap->type)];
                        })
                        ->filter()
                        ->all(),
                ];
            })
            ->all();
    }

    /**
     * The measure the user spent on the recap item's title.
     *
     * @param RecapItem   $recapItem
     * @param string|null $recapType
     *
     * @return string|null
     */
    protected function recapItemDetail(RecapItem $recapItem, ?string $recapType = null): ?string
    {
        $minutes = (int) round($recapItem->parts_duration / 60);
        $count = number_format($recapItem->parts_count);

        if (!$recapItem->parts_count && !$minutes) {
            return null;
        }

        return match ($recapType ?? $recapItem->model_type) {
            Manga::class => $recapItem->parts_count ? __(':x chapters', ['x' => $count]) : null,
            Game::class => $minutes ? __(':x minutes', ['x' => number_format($minutes)]) : null,
            Studio::class, Genre::class, Theme::class => $minutes ? __(':x minutes', ['x' => number_format($minutes)]) : null,
            Character::class => null,
            Person::class => trans_choice(':count character|:count characters', $recapItem->parts_count, ['count' => $count]),
            MediaStaff::class => collect([$recapItem->role?->name, trans_choice(':count title|:count titles', $recapItem->parts_count, ['count' => $count])])->filter()->join(' · '),
            default => $minutes ? __(':x minutes', ['x' => number_format($minutes)]) : __(':x episodes', ['x' => $count]),
        };
    }

    /**
     * The section titles of the selected period keyed by recap type.
     *
     * @return array
     */
    #[Computed]
    public function sectionTitles(): array
    {
        if ($this->month === 0) {
            $year = ['x' => $this->year];

            return [
                Anime::class => __('Your Top Anime of :x', $year),
                Manga::class => __('Your Top Manga of :x', $year),
                Game::class => __('Your Top Games of :x', $year),
                Genre::class => __('Your Top Genres of :x', $year),
                Theme::class => __('Your Top Themes of :x', $year),
                Studio::class => __('Your Top Studios of :x', $year),
                Character::class => __('Your Top Characters of :x', $year),
                Person::class => __('Your Top Voices of :x', $year),
                MediaStaff::class => __('Your Top Creators of :x', $year),
            ];
        }

        return [
            Anime::class => __('Your Top Anime'),
            Manga::class => __('Your Top Manga'),
            Game::class => __('Your Top Games'),
            Genre::class => __('Your Top Genres'),
            Theme::class => __('Your Top Themes'),
            Studio::class => __('Your Top Studios'),
            Character::class => __('Your Top Characters'),
            Person::class => __('Your Top Voices'),
            MediaStaff::class => __('Your Top Creators'),
        ];
    }

    /**
     * The top title of each month of the selected year keyed by type.
     *
     * @return array
     */
    #[Computed]
    public function topTitlesByMonth(): array
    {
        if ($this->month !== 0) {
            return [];
        }

        $titles = [
            Anime::class => ['title' => __('Your Top Anime by Month'), 'models' => collect(), 'eyebrows' => []],
            Manga::class => ['title' => __('Your Top Manga by Month'), 'models' => collect(), 'eyebrows' => []],
            Game::class => ['title' => __('Your Top Games by Month'), 'models' => collect(), 'eyebrows' => []],
        ];

        auth()->user()->recaps()
            ->with([
                'recapItems' => function (HasMany $query) {
                    $query->where('position', '=', 1)
                        ->with([
                            'model' => function (MorphTo $morphTo) {
                                $withTitleRelations = function (Builder $query) {
                                    $query->with([
                                        'genres', 'mediaStat', 'media', 'translation', 'tvRating', 'themes',
                                        'library' => function ($query) {
                                            $query->where('user_id', '=', auth()->id());
                                        },
                                    ]);
                                };

                                $morphTo->constrain([
                                    Anime::class => $withTitleRelations,
                                    Game::class => $withTitleRelations,
                                    Manga::class => $withTitleRelations,
                                ]);
                            }
                        ]);
                }
            ])
            ->where('year', '=', $this->year)
            ->whereBetween('month', [1, 12])
            ->whereIn('type', array_keys($titles))
            ->orderBy('month')
            ->get()
            ->each(function (Recap $recap) use (&$titles) {
                $model = $recap->recapItems->first()?->model;

                if ($model === null) {
                    return;
                }

                $titles[$recap->type]['eyebrows'][$titles[$recap->type]['models']->count()] = now()->startOfYear()->month($recap->month)->translatedFormat('F');
                $titles[$recap->type]['models']->push($model);
            });

        return array_filter($titles, fn (array $section) => $section['models']->isNotEmpty());
    }

    /**
     * The stat cards of the selected period grouped by section.
     *
     * @return array
     */
    #[Computed]
    public function recapStatCards(): array
    {
        $recapStats = RecapStat::where([
            ['user_id', '=', auth()->id()],
            ['year', '=', $this->year],
            ['month', '=', $this->month],
        ])
            ->with([
                'model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Anime::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                    ]);
                },
            ])
            ->get()
            ->keyBy(fn (RecapStat $recapStat) => $recapStat->stat->value);

        $value = fn (int $stat) => $recapStats->get($stat)?->value;
        $cards = [
            'habits' => [],
            'activity' => [],
        ];

        if ($weekday = $value(RecapStatType::BusiestWeekday)) {
            $cards['habits'][] = ['title' => __('Busiest Day'), 'value' => now()->startOfWeek()->addDays($weekday - 1)->translatedFormat('l'), 'caption' => null];
        }

        if (($hour = $value(RecapStatType::BusiestHour)) !== null) {
            $cards['habits'][] = ['title' => __('Favorite Time'), 'value' => now()->startOfDay()->setHour($hour)->translatedFormat('g A'), 'caption' => null];
        }

        if ($streak = $recapStats->get(RecapStatType::LongestStreak)) {
            $cards['habits'][] = ['title' => __('Longest Streak'), 'value' => trans_choice(':count day|:count days', $streak->value, ['count' => number_format($streak->value)]), 'caption' => $streak->occurred_at ? __('Ended :x', ['x' => $streak->occurred_at->translatedFormat('M j')]) : null];
        }

        if ($binge = $recapStats->get(RecapStatType::BiggestBinge)) {
            $cards['habits'][] = ['title' => __('Biggest Binge'), 'value' => trans_choice(':count episode|:count episodes', $binge->value, ['count' => number_format($binge->value)]), 'caption' => $binge->occurred_at?->translatedFormat('M j')];
        }

        foreach ([RecapStatType::FirstTitle => __('First Watch'), RecapStatType::LastTitle => __('Last Watch')] as $stat => $title) {
            $recapStat = $recapStats->get($stat);

            if ($recapStat?->model !== null) {
                $cards['habits'][] = ['title' => $title, 'value' => $recapStat->model->title, 'caption' => trans_choice(':count episode|:count episodes', $recapStat->value, ['count' => number_format($recapStat->value)])];
            }
        }

        if (($provider = $recapStats->get(RecapStatType::TopProvider))?->model !== null) {
            $cards['habits'][] = ['title' => __('Where You Watched'), 'value' => $provider->model->original_name, 'caption' => trans_choice(':count episode|:count episodes', $provider->value, ['count' => number_format($provider->value)])];
        }

        if ($ratingsGiven = $value(RecapStatType::RatingsGiven)) {
            $averageRating = $value(RecapStatType::AverageRating);
            $cards['activity'][] = ['title' => __('Ratings Given'), 'value' => number_format($ratingsGiven), 'caption' => $averageRating ? __('Average of :x', ['x' => number_format($averageRating / 100, 1)]) : null];
        }

        foreach ([
            RecapStatType::ReviewsWritten => __('Reviews Written'),
            RecapStatType::TitlesCompleted => __('Titles Completed'),
            RecapStatType::TitlesAdded => __('Titles Added'),
            RecapStatType::TitlesDropped => __('Titles Dropped'),
            RecapStatType::FavoritesAdded => __('Favorites Added'),
            RecapStatType::AchievementsEarned => __('Achievements Earned'),
        ] as $stat => $title) {
            if ($count = $value($stat)) {
                $cards['activity'][] = ['title' => $title, 'value' => number_format($count), 'caption' => null];
            }
        }

        return $cards;
    }

    /**
     * The name of the selected period.
     *
     * @return string
     */
    public function getPeriodNameProperty(): string
    {
        if ($this->month === 0) {
            return (string) $this->year;
        }

        return now()->startOfYear()->month($this->month)->monthName;
    }

    /**
     * The localized period heading split around the period name.
     *
     * @return array
     */
    public function getPeriodHeadingPartsProperty(): array
    {
        $placeholder = '%period%';
        $heading = __('Series that defined your arc in :x', ['x' => $placeholder]);

        return array_pad(explode($placeholder, $heading, 2), 2, '');
    }

    /**
     * The recap whose colors theme the page.
     *
     * @return Recap
     */
    public function getBackdropRecapProperty(): Recap
    {
        return Recap::make([
            'year' => $this->year,
        ]);
    }

    /**
     * The shareable image cards of the selected period keyed by the button that shares them.
     *
     * @return array
     */
    #[Computed]
    public function shareCards(): array
    {
        $brand = [
            'wordmark' => __('Re:CAP'),
            'name' => config('app.name'),
            'colors' => [$this->backdropRecap->background_color1, $this->backdropRecap->background_color2],
        ];
        $cards = [];

        foreach ([Genre::class => ['genres', __('Top Genres')], Theme::class => ['themes', __('Top Themes')]] as $type => [$key, $title]) {
            $recapItems = $this->recaps->firstWhere('type', $type)?->recapItems->whereNotNull('model')->take(5);

            if ($recapItems?->isNotEmpty()) {
                $cards[$key] = [
                    'layout' => 'genres',
                    'title' => $title,
                    'subtitle' => $this->sharePeriodName,
                    'items' => $recapItems->map(fn (RecapItem $recapItem) => [
                        'name' => $recapItem->model->name,
                        'detail' => $this->recapItemDetail($recapItem, $type),
                    ])->values()->all(),
                ];
            }
        }

        foreach ($this->recapComparisons as $index => $recapComparison) {
            $cards['comparison-' . $index] = [
                'layout' => 'comparison',
                'title' => __('Compare Your Re:CAP'),
                'subtitle' => $recapComparison['title'],
                'entries' => [
                    [
                        'year' => (string) $recapComparison['recap']->year,
                        'name' => $recapComparison['currentModel']->title,
                        'detail' => $recapComparison['currentDetail'],
                        'artwork' => $this->shareArtwork($recapComparison['currentModel']),
                    ],
                    [
                        'year' => (string) ($recapComparison['recap']->year - 1),
                        'name' => $recapComparison['previousModel']->title,
                        'detail' => $recapComparison['previousDetail'],
                        'artwork' => $this->shareArtwork($recapComparison['previousModel']),
                    ],
                ],
            ];
        }

        // The first four sections the period has data for.
        $sections = collect([
            Anime::class => __('Top Anime'),
            Manga::class => __('Top Manga'),
            Game::class => __('Top Games'),
            Character::class => __('Top Characters'),
            Person::class => __('Top Voices'),
            MediaStaff::class => __('Top Creators'),
        ])
            ->map(function (string $title, string $type) {
                $models = $this->recaps->firstWhere('type', $type)?->recapItems->pluck('model')->filter()->values();

                if ($models === null || $models->isEmpty()) {
                    return null;
                }

                return [
                    'title' => $title,
                    'items' => $models->take(10)->map(fn ($model) => match (true) {
                        $model instanceof Character => $model->name,
                        $model instanceof Person => $model->full_name,
                        default => $model->title,
                    })->all(),
                    'artwork' => $this->shareArtwork($models->first()),
                ];
            })
            ->filter()
            ->take(4)
            ->values();

        if ($sections->isNotEmpty()) {
            $totalMinutes = (int) round($this->recaps->whereIn('type', [Anime::class, Manga::class, Game::class])->sum('total_parts_duration') / 60);

            $cards['summary'] = [
                'layout' => 'summary',
                'period' => $this->sharePeriodName,
                'total' => $totalMinutes ? __(':x minutes', ['x' => number_format($totalMinutes)]) : null,
                'sections' => $sections->all(),
            ];
        }

        return [
            'brand' => $brand,
            'fileName' => str('kurozora-recap-' . $this->year . ($this->month ? '-' . $this->month : ''))->slug()->value(),
            'cards' => $cards,
        ];
    }

    /**
     * The name of the selected period on share cards.
     *
     * @return string
     */
    public function getSharePeriodNameProperty(): string
    {
        if ($this->month === 0) {
            return (string) $this->year;
        }

        return now()->startOfYear()->year($this->year)->month($this->month)->translatedFormat('F Y');
    }

    /**
     * The artwork a share card shows for the given model.
     *
     * @param Anime|Manga|Game|Character|Person $model
     *
     * @return array
     */
    protected function shareArtwork(Anime|Manga|Game|Character|Person $model): array
    {
        $isProfile = $model instanceof Character || $model instanceof Person;
        $collection = $isProfile ? MediaCollection::Profile() : MediaCollection::Poster();

        return [
            'url' => $model->getFirstMediaFullUrl($collection) ?? asset($isProfile ? 'images/static/placeholders/person_poster.webp' : 'images/static/placeholders/anime_poster.webp'),
            'color' => $model->getFirstMedia($collection->value)?->custom_properties['background_color'] ?? null,
            'shape' => match (true) {
                $model instanceof Manga => 'book',
                $model instanceof Game => 'square',
                $model instanceof Character, $model instanceof Person => 'circle',
                default => 'poster',
            },
        ];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.recap.index');
    }
}
