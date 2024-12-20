<?php

namespace App\Livewire\Recap;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Recap;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
        if (empty($this->year) || !ctype_digit($this->year)) {
            $this->year = now()->year;
        }

        if ($this->year === now()->year && now()->month !== 12) {
            $this->month = now()->subMonth()->month;
        } else {
            $this->month = 12;
        }
    }

    public function updatingYear(int $year): void
    {
        if ($year === now()->year) {
            if (now()->month !== 12) {
                $this->month = now()->subMonth()->month;
            } else {
                now()->month = 12;
            }
        } else {
            $this->month = 12;
        }
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
    public function getRecapsProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $recaps = auth()->user()->recaps()
            ->with([
                'recapItems.model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Anime::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translation', 'tv_rating', 'themes'])
                                ->when(auth()->user(), function ($query, $user) {
                                    return $query->with(['library' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
                        },
                        Game::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translation', 'tv_rating', 'themes'])
                                ->when(auth()->user(), function ($query, $user) {
                                    return $query->with(['library' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
                        },
                        Manga::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translation', 'tv_rating', 'themes'])
                                ->when(auth()->user(), function ($query, $user) {
                                    return $query->with(['library' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
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
    public function getRecapYearsProperty(): Collection
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
     * Get the user's recap months.
     *
     * @return Collection
     */
    public function getRecapMonthsProperty(): Collection
    {
        $recapMonths = auth()->user()->recaps()
            ->select('month', 'year')
            ->distinct()
            ->where('year', '=', $this->year)
            ->orderBy('month')
            ->get();

        if (now()->year === $this->year && now()->month !== 12) {
            $recapMonths->push(Recap::make([
                'month' => now()->month,
            ]));
        }

        return $recapMonths;
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
