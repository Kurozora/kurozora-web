<?php

namespace App\Livewire\Browse\Anime\Continuing;

use App\Models\Anime;
use App\Traits\Livewire\WithAnimeSearch;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Index extends Component
{
    use WithAnimeSearch {
        getSearchResultsProperty as protected getParentSearchResultsProperty;
    }

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Redirect the user to a random anime.
     *
     * @return void
     */
    public function randomAnime(): void
    {
        $anime = Anime::where([
            ['status_id', '=', 3],
            ['started_at', '<=', season_of_year()->startDate()->toDateString()],
        ])
            ->inRandomOrder()
            ->first();

        $this->redirectRoute('anime.details', $anime);
    }

    /**
     * Build a 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     *
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        // Season of Year is calculated on month level, so the year value is `0`.
        // Here we require the start date of the current year's season, so we have
        // to manually set the year.
        $seasonStartDate = season_of_year()
            ->startDate()
            ->setYear(now()->year);

        return $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->where([
                [static::$searchModel::TABLE_NAME . '.status_id', '=', 3],
                [static::$searchModel::TABLE_NAME . '.started_at', '<=', $seasonStartDate->toDateString()],
            ])
            ->orderBy('started_at', 'desc');
    }

    /**
     * Build a 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     *
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        // Season of Year is calculated on month level, so the year value is `0`.
        // Here we require the start date of the current year's season, so we have
        // to manually set the year.
        $seasonStartDate = season_of_year()
            ->startDate()
            ->setYear(now()->year);

        return $query->where('status_id', 3)
            ->where('started_at', ['<=', $seasonStartDate->timestamp])
            ->query(function (EloquentBuilder $query) {
                $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                    ->when(auth()->user(), function ($query, $user) {
                        $query->with(['library' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    });
            });
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
     * The computed search results property.
     *
     * @return array|LengthAwarePaginator
     */
    public function getSearchResultsProperty(): array|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return [];
        }

        return $this->getParentSearchResultsProperty();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.anime.continuing.index');
    }
}
