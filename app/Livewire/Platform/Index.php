<?php

namespace App\Livewire\Platform;

use App\Livewire\BaseSearchIndex;
use App\Traits\Livewire\WithPlatformSearch;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Index extends BaseSearchIndex
{
    use WithPlatformSearch {
        getSearchResultsProperty as protected parentGetSearchResultsProperty;
    }

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.platform.index';

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
}
