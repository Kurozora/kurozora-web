<?php

namespace App\Livewire\Season;

use App\Events\SeasonViewed;
use App\Models\Season;
use App\Traits\Livewire\WithEpisodeSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Episodes extends Component
{
    use WithEpisodeSearch {
        getSearchResultsProperty as protected getParentSearchResultsProperty;
        searchIndexQuery as protected parentSearchIndexQuery;
        searchQuery as protected parentSearchQuery;
    }

    /**
     * The object containing the season data.
     *
     * @var Season $season
     */
    public Season $season;

    /**
     * Determines whether to load the page.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'update-season' => '$refresh'
    ];

    /**
     * Prepare the component.
     *
     * @param Season $season
     *
     * @return void
     */
    public function mount(Season $season): void
    {
        // Call the SeasonViewed event
        SeasonViewed::dispatch($season);

        $this->season = $season;
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
     * Build an 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $this->parentSearchIndexQuery($query)
            ->with([
                'anime' => function ($query) {
                    $query->with(['media', 'translations']);
                },
                'media',
                'season' => function ($query) {
                    $query->with(['translations']);
                },
                'translations'
            ]);
    }

    /**
     * Build an 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $this->parentSearchQuery($query)
            ->query(function (EloquentBuilder $query) {
                $query->with([
                    'anime' => function ($query) {
                        $query->with(['media', 'translations']);
                    },
                    'media',
                    'season' => function ($query) {
                        $query->with(['translations']);
                    },
                    'translations'
                ]);
            });
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

        return $this->getParentSearchResultsProperty();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.season.episodes');
    }
}
