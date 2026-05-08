<?php

namespace App\Traits\Livewire;

use App\Models\Character;
use App\Models\Episode;
use App\Models\UserLibrary;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as ConcreteLengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;

trait WithSearch
{
    use WithPagination;

    /**
     * The search string.
     *
     * @var string $search
     */
    public string $search = '';

    /**
     * The selected index letter.
     *
     * @var string $letter
     */
    public string $letter = '';

    /**
     * The selected search type.
     *
     * @var string $type
     */
    public string $type = 'all';

    /**
     * The selected search type's value.
     *
     * @var $typeValue
     */
    public $typeValue = 0;

    /**
     * The component's filter attributes.
     *
     * @var array $filter
     */
    public array $filter = [];

    /**
     * The component's order attributes.
     *
     * @var array $order
     */
    public array $order = [];

    /**
     * The component's search scopes.
     *
     * @var array
     */
    public array $searchTypes = [];

    /**
     * The query strings of the component.
     *
     * @return string[]
     */
    protected function queryString(): array
    {
        return [
            'search' => ['as' => 'q', 'except' => ''],
            'letter' => ['except' => ''],
            'type' => ['except' => 'all'],
            'perPage' => ['except' => 25],
        ];
    }

    /**
     * The rules of the component.
     *
     * @return string[][]
     */
    protected function rules(): array
    {
        return [
            'search' => ['string', 'min:1'],
            'letter' => ['string', 'max:1'],
            'type' => ['nullable', 'string', 'distinct', 'in:' . implode(',', array_values($this->searchTypes))],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:25'],
        ];
    }

    /**
     * The column used for the letter index query.
     *
     * @return string
     */
    protected function letterIndexColumn(): string
    {
        return 'original_title';
    }

    /**
     * The column used for the letter index query.
     *
     * @return string
     */
    protected function typeColumn(): string
    {
        return 'media_type_id';
    }

    /**
     * Determines whether the user is performing a search.
     *
     * @return bool
     */
    public function isSearching(): bool
    {
        $isOrdering = $this->isOrdering();
        $isFiltering = $this->isFiltering();

        return !empty($this->search)
            || $isOrdering
            || $isFiltering
            || !empty($this->typeValue)
            || !empty($this->letter);
    }

    /**
     * Determines whether an ordering is applied.
     *
     * @return bool
     */
    public function isOrdering(): bool
    {
        return collect($this->order)->contains('selected', '!=', null);
    }

    /**
     * Determines whether a filter is applied.
     *
     * @return bool
     */
    public function isFiltering(): bool
    {
        return collect($this->filter)->contains('selected', '!=', null);
    }

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mountWithSearch(): void
    {
        $this->filter = $this->setFilterableAttributes();
        $this->order = $this->setOrderableAttributes();
        $this->searchTypes = $this->setSearchTypes();
        $this->updatedType($this->type);
    }

    /**
     * Called when the `type` property is updated.
     *
     * @param string $newValue
     *
     * @return void
     */
    public function updatedType(string $newValue): void
    {
        if ($newValue === 'all') {
            $this->typeValue = 0;
            return;
        }

        $this->typeValue = collect($this->searchTypes)
            ->search(function ($value) use ($newValue) {
                return str($value)->slug()->value() === $newValue;
            });
    }

    private function getOrders(): array
    {
        $orders = [];
        foreach ($this->order as $attribute => $order) {
            $attribute = str_replace(':', '.', $attribute);
            $selected = $order['selected'];

            if (!empty($selected)) {
                $orders[] = [
                    'column' => $attribute,
                    'direction' => $selected,
                ];
            }
        }
        return $orders;
    }

    private function getWheres(): array
    {
        $wheres = [];
        $whereIns = [];
        foreach ($this->filter as $attribute => $filter) {
            if ($attribute == 'library_status') {
                continue;
            }

            $attribute = str_replace(':', '.', $attribute);
            $selected = $filter['selected'];
            $type = $filter['type'];

            if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                if ($type === 'multiselect') {
                    $whereIns[$attribute] = $selected;
                } else {
                    $wheres[$attribute] = match ($type) {
                        'date' => Carbon::createFromFormat('Y-m-d', $selected)
                            ?->setTime(0, 0)
                            ->timestamp,
                        'time' => $selected . ':00',
                        'double' => number_format($selected, 2, '.', ''),
                        default => $selected,
                    };
                }
            }
        }
        return [$wheres, $whereIns];
    }

    /**
     * The computed search results property.
     *
     * @return ?LengthAwarePaginator
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
        // Order
        $orders = $this->getOrders();

        // Filter
        [$wheres, $whereIns] = $this->getWheres();

        // Get library status
        $userLibraryStatuses = $this->filter['library_status']['selected'] ?? null;
        $user = auth()->user();

        // If no search, filter or order was performed, return the model's index
        if (empty($this->search) && (empty($wheres) && empty($whereIns)) && empty($orders)) {
            if ($userLibraryStatuses) {
                $models = $user
                    ->whereTracked(static::$searchModel)
                    ->withoutIgnoreList()
                    ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                    ->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }])
                    ->wherePivotIn('status', $userLibraryStatuses);
            } else {
                $models = static::$searchModel::query();
            }

            $models = $models
                ->when(!empty($this->typeValue), function (EloquentBuilder $query) {
                    $query->where($this->typeColumn(), '=', $this->typeValue);
                })
                ->when(!empty($this->letter), function (EloquentBuilder $query) {
                    if (static::$searchModel === Episode::class || static::$searchModel === Character::class) {
                        $query->whereRelation('translations', function ($query) {
                            $query->where('locale', '=', 'en');

                            if ($this->letter == '.') {
                                $query->whereRaw($this->letterIndexColumn() . ' REGEXP \'^[^a-zA-Z]*$\'');
                            } else {
                                $query->whereLike($this->letterIndexColumn(), $this->letter . '%');
                            }
                        });
                    } else {
                        if ($this->letter == '.') {
                            $query->whereRaw($this->letterIndexColumn() . ' REGEXP \'^[^a-zA-Z]*$\'');
                        } else {
                            $query->whereLike($this->letterIndexColumn(), $this->letter . '%');
                        }
                    }
                });

            return $this->searchIndexQuery($models)
                ->paginate($this->perPage);
        }

        // Search
        if ($userLibraryStatuses) {
            return $this->paginateLibraryScopedSearch(
                modelClass: static::$searchModel,
                userId: $user->id,
                statuses: $userLibraryStatuses,
                wheres: $wheres,
                whereIns: $whereIns,
                orders: $orders,
            );
        }

        if (!empty($this->letter)) {
            $wheres['letter'] = $this->letter;
        }

        if (!empty($this->typeValue)) {
            $wheres[$this->typeColumn()] = $this->typeValue;
        }

        $models = static::$searchModel::search($this->search);
        $models->wheres = $wheres;
        $models->whereIns = $whereIns;
        $models->orders = $orders;
        $models = $this->searchQuery($models);

        // Paginate
        return $models->paginate($this->perPage);
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
        return $query;
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
        return $query;
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return array
     */
    public function setOrderableAttributes(): array
    {
        return [];
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        return [];
    }

    /**
     * Set the search types of the model.
     *
     * @return array
     */
    public function setSearchTypes(): array
    {
        return [];
    }

    /**
     * Reset order to default values.
     *
     * @return void
     */
    public function resetOrder(): void
    {
        $this->order = array_map(function ($order) {
            $order['selected'] = null;
            return $order;
        }, $this->order);
    }

    /**
     * Reset filter to default values.
     *
     * @return void
     */
    public function resetFilter(): void
    {
        $this->filter = array_map(function ($filter) {
            $filter['selected'] = null;
            return $filter;
        }, $this->filter);
    }

    /**
     * The computed collection of letter indexes.
     *
     * @return Collection
     */
    public function getLetteredIndexProperty(): Collection
    {
        $keys = range('A', 'Z');
        $values = range('a', 'z');

        return collect($keys)
            ->combine($values)
            ->prepend('.', '#');
    }

    /**
     * Searches the user's library for the given trackable class.
     *
     * @param string       $modelClass
     * @param int          $userId
     * @param array        $statuses
     * @param bool         $excludeHidden
     * @param array        $wheres
     * @param array        $whereIns
     * @param array        $orders
     * @param null|Closure $hydrate
     *
     * @return LengthAwarePaginator
     */
    protected function paginateLibraryScopedSearch(
        string   $modelClass,
        int      $userId,
        array    $statuses = [],
        bool     $excludeHidden = false,
        array    $wheres = [],
        array    $whereIns = [],
        array    $orders = [],
        ?Closure $hydrate = null,
    ): LengthAwarePaginator
    {
        if (!empty($this->letter)) {
            $wheres['letter'] = $this->letter;
        }

        if (!empty($this->typeValue)) {
            $wheres[$this->typeColumn()] = $this->typeValue;
        }

        $libraryQuery = UserLibrary::query()
            ->where('user_id', $userId)
            ->where('trackable_type', $modelClass);

        if (!empty($statuses)) {
            $libraryQuery->whereIn('status', $statuses);
        }

        if ($excludeHidden) {
            $libraryQuery->where('is_hidden', false);
        }

        $libraryIds = $libraryQuery->pluck('trackable_id')->all();

        if (empty($libraryIds)) {
            return $this->emptyLibraryPaginator();
        }

        if ($this->search === '') {
            $matchedIds = $libraryIds;
        } else {
            $librarySet = array_flip($libraryIds);
            $cap = (int) config('scout.library_search_relevance_cap', 10000);

            $search = $modelClass::search($this->search)
                ->options(['attributesToRetrieve' => ['id']]);
            $search->wheres = $wheres;
            $search->whereIns = $whereIns;

            $hits = $search->take($cap)->raw()['hits'] ?? [];

            $matchedIds = [];
            foreach ($hits as $hit) {
                $id = $hit['id'] ?? null;

                if ($id !== null && isset($librarySet[$id])) {
                    $matchedIds[] = $id;
                }
            }

            if (empty($matchedIds)) {
                return $this->emptyLibraryPaginator();
            }
        }

        $modelQuery = $modelClass::whereIn('id', $matchedIds);

        if ($hydrate !== null) {
            $hydrate($modelQuery);
        }

        $models = $modelQuery->get()->keyBy('id');

        if (empty($orders)) {
            $sorted = collect($matchedIds)
                ->map(fn($id) => $models->get($id))
                ->filter()
                ->values();
        } else {
            $sorted = $models->values();

            foreach (array_reverse($orders) as $order) {
                $sorted = $order['direction'] === 'asc'
                    ? $sorted->sortBy($order['column'])
                    : $sorted->sortByDesc($order['column']);
            }

            $sorted = $sorted->values();
        }

        $page = max(1, Paginator::resolveCurrentPage());
        $perPage = $this->perPage;
        $total = $sorted->count();
        $items = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        return new ConcreteLengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()],
        );
    }

    /**
     * Returns an empty paginator.
     *
     * @return LengthAwarePaginator
     */
    protected function emptyLibraryPaginator(): LengthAwarePaginator
    {
        return new ConcreteLengthAwarePaginator(
            [],
            0,
            $this->perPage,
            max(1, Paginator::resolveCurrentPage()),
            ['path' => Paginator::resolveCurrentPath()],
        );
    }
}
