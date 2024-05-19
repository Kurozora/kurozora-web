<?php

namespace App\Livewire\Recap;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
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
     * @var ?int $year
     */
    public ?int $year = null;

    /**
     * Determines whether to load the page.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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
    public function getQueryString(): array
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
        if (empty($this->year)) {
            $this->year = 2023;
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
                        Manga::class => function (Builder $query) {
                            $query->with(['genres', 'mediaStat', 'media', 'translations', 'tv_rating', 'themes'])
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
            ->get();

        $this->loadingScreenEnabled = false;

        return $recaps;
    }

    /**
     * Get the user's recap years.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getRecapYearsProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $recapYears = auth()->user()->recaps()
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $this->loadingScreenEnabled = false;

        return $recapYears;
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
