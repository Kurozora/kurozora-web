<?php

namespace App\Livewire\Browse\Game\Upcoming;

use App\Models\Game;
use App\Traits\Livewire\WithGameSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Index extends Component
{
    use WithGameSearch {
        getSearchResultsProperty as protected getParentSearchResultsProperty;
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
     * Build an 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->where('published_at', '>=', yesterday());
    }

    /**
     * Build an 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->where('published_at', ['>=', yesterday()->timestamp])
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
        return view('livewire.browse.game.upcoming.index');
    }
}
