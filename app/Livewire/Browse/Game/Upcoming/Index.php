<?php

namespace App\Livewire\Browse\Game\Upcoming;

use App\Models\Game;
use App\Traits\Livewire\WithGameSearch;
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
    use WithGameSearch {
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
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
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
     * Redirect the user to a random game.
     *
     * @return void
     */
    public function randomGame(): void
    {
        $game = Game::where('published_at', '>=', yesterday())
            ->inRandomOrder()
            ->first();
        $this->redirectRoute('games.details', $game);
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
        return $this->parentSearchIndexQuery($query)
            ->where(static::$searchModel::TABLE_NAME . '.published_at', '>=', yesterday());
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
        return $this->parentSearchQuery($query)
            ->where('published_at', ['>=', yesterday()->timestamp]);
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
        return view('livewire.browse.game.upcoming.index');
    }
}
