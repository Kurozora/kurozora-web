<?php

namespace App\Livewire\Season;

use App\Events\ModelViewed;
use App\Models\Anime;
use App\Models\Season;
use App\Traits\Livewire\WithEpisodeSearch;
use Artisan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;

class Episodes extends Component
{
    use WithEpisodeSearch {
        getSearchResultsProperty as protected parentGetSearchResultsProperty;
    }

    /**
     * The object containing the season data.
     *
     * @var Season $season
     */
    public Season $season;

    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

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
        // Call the ModelViewed event
        ModelViewed::dispatch($season, request()->ip());

        $this->season = $season->loadMissing([
            'anime' => function ($query) {
                $query->withoutGlobalScopes()
                    ->with(['media', 'translations']);
            },
            'media',
            'translations'
        ]);
        $this->anime = $this->season->anime;
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
     * Runs a command to update episode data.
     *
     * @return void
     */
    public function updateEpisodes(): void
    {
        Artisan::call('scrape:tvdb_episode', ['tvdbID' => $this->anime->tvdb_id]);
    }

    /**
     * Determines whether episode data can be updated.
     *
     * @return bool
     */
    public function getCanUpdateEpisodesProperty(): Bool
    {
        return $this->anime->tvdb_id != null && $this->anime->season_count == 1;
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
