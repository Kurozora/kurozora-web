<?php

namespace App\Livewire\Manga;

use App\Models\MediaType;
use App\Traits\Livewire\WithMangaSearch;
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
    use WithMangaSearch {
        getSearchResultsProperty as protected getParentSearchResultsProperty;
        queryString as protected parentQueryString;
    }

    /**
     * The type query parameter.
     *
     * @var string $typeQuery
     */
    public string $typeQuery = 'all';

    /**
     * The type of the search.
     *
     * @var int $type
     */
    public int $type = 0;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The query strings of the component.
     *
     * @return string[]
     */
    protected function queryString(): array
    {
        $queryString = $this->parentQueryString();
        $queryString['typeQuery'] = ['except' => 'all', 'as' => 'type'];
        return $queryString;
    }

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

    /**
     * @param string $newValue
     *
     * @return void
     */
    public function updatedTypeQuery(string $newValue): void
    {
        if ($newValue === 'all') {
            $this->type = 0;
            return;
        }

        $this->type = $this->searchScopes
            ->search(function ($value) use ($newValue) {
                return str($value)->slug()->value() === $newValue;
            });
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
        return $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->when(!empty($this->type), function (EloquentBuilder $query) {
                $query->whereRelation('media_type', 'id', '=', $this->type);
            })
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });
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
        return $query->query(function (EloquentBuilder $query) {
            $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->when(!empty($this->letter), function (EloquentBuilder $query) {
                    if ($this->letter == '.') {
                        $query->whereRaw('original_title REGEXP \'^[^a-zA-Z]*$\'');
                    } else {
                        $query->whereLike('original_title', $this->letter . '%');
                    }
                })
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                });
        })
            ->when(!empty($this->type), function (ScoutBuilder $query) {
                $query->where('media_type_id', $this->type);
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
     * The computed collection of manga types.
     *
     * @return Collection
     */
    public function getSearchScopesProperty(): Collection
    {
        return MediaType::where('type', '=', 'manga')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(__('All'), '');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.manga.index');
    }
}
