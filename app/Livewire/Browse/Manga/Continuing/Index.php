<?php

namespace App\Livewire\Browse\Manga\Continuing;

use App\Models\Manga;
use App\Traits\Livewire\WithMangaSearch;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Index extends Component
{
    use WithMangaSearch {
        getSearchResultsProperty as protected parentGetSearchResultsProperty;
        searchIndexQuery as protected parentSearchIndexQuery;
        searchQuery as protected parentSearchQuery;
    }

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Redirect the user to a random manga.
     *
     * @return void
     */
    public function randomManga(): void
    {
        $manga = Manga::where([
            ['status_id', '=', 3],
            ['started_at', '<=', season_of_year()->startDate()->toDateString()],
        ])
            ->inRandomOrder()
            ->first();

        $this->redirectRoute('manga.details', $manga);
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

        return $this->parentSearchIndexQuery($query)
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

        return $this->parentSearchQuery($query)
            ->where('status_id', 3)
            ->where('started_at', ['<=', $seasonStartDate->timestamp]);
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
     * @return Collection|LengthAwarePaginator
     */
    public function getSearchResultsProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->parentGetSearchResultsProperty();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.manga.continuing.index');
    }
}
