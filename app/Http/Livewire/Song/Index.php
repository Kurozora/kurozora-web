<?php

namespace App\Http\Livewire\Song;

use App\Traits\Livewire\WithSongSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Index extends Component
{
    use WithSongSearch {
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
        return view('livewire.song.index');
    }
}
