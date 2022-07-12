<?php

namespace App\Traits\Livewire;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
     * Prepare the component.
     *
     * @return void
     */
    public function mountWithSearch(): void
    {
        $this->setFilterableAttributes();
        $this->setOrderableAttributes();
    }

    /**
     * The computed search results property.
     *
     * @return ?LengthAwarePaginator
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
        // Order
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

        // Filter
        $wheres = [];
        foreach ($this->filter as $attribute => $filter) {
            $attribute = str_replace(':', '.', $attribute);
            $selected = $filter['selected'];
            $type = $filter['type'];

            if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                $wheres[$attribute] = match ($type) {
                    'date' => Carbon::createFromFormat('Y-m-d', $selected)
                        ->setTime(0, 0)
                        ->timestamp,
                    'time' => $selected . ':00',
                    'double' => number_format($selected, 2, '.', ''),
                    default => $selected,
                };
            }
        }

        // If no search was performed, return all anime
        if (empty($this->search) && empty($wheres) && empty($orders)) {
            $model = static::$searchModel::query();
            $model = $this->searchIndexQuery($model);
            return $model->paginate($this->perPage);
        }

        // Search
        $model = static::$searchModel::search($this->search);
        $model->wheres = $wheres;
        $model->orders = $orders;
        $model = $this->searchQuery($model);

        // Paginate
        return $model->paginate($this->perPage);
    }

    /**
     * Build an 'search index' query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchIndexQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query;
    }

    /**
     * Build an 'search' query for the given resource.
     *
     * @param \Laravel\Scout\Builder $query
     * @return \Laravel\Scout\Builder
     */
    public function searchQuery(\Laravel\Scout\Builder $query): \Laravel\Scout\Builder
    {
        return $query;
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return void
     */
    public function setOrderableAttributes(): void
    {
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return void
     */
    public function setFilterableAttributes(): void
    {
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
}
