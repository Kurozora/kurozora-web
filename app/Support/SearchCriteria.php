<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class SearchCriteria
{
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
     * The slug of the selected search type, empty for all.
     *
     * @var string $type
     */
    public string $type = '';

    /**
     * The key of the selected search type.
     *
     * @var int|string|null $typeValue
     */
    public int|string|null $typeValue = null;

    /**
     * The number of results per page.
     *
     * @var int $perPage
     */
    public int $perPage = 25;

    /**
     * The filterable attributes with their selected values.
     *
     * @var array $filter
     */
    public array $filter = [];

    /**
     * The orderable attributes with their selected directions.
     *
     * @var array $order
     */
    public array $order = [];

    /**
     * The selectable search types keyed by their value.
     *
     * @var array $searchTypes
     */
    public array $searchTypes = [];

    /**
     * The slug of the search type selected when the query string names none.
     *
     * @var string $defaultType
     */
    public string $defaultType = '';

    /**
     * Builds the criteria the query string of the given request describes.
     *
     * @param Request $request
     * @param array   $filters
     * @param array   $orders
     * @param array   $searchTypes
     * @param string  $defaultType
     *
     * @return self
     */
    public static function fromRequest(Request $request, array $filters = [], array $orders = [], array $searchTypes = [], string $defaultType = ''): self
    {
        $criteria = new self();
        $criteria->search = trim($request->string('q')->value());
        $criteria->searchTypes = $searchTypes;
        $criteria->defaultType = $defaultType;
        $criteria->setLetter($request->string('letter')->value());
        $criteria->setType($request->string('type')->value());
        $criteria->setPerPage($request->integer('perPage', 25));
        $criteria->setFilter($filters, $request->input('filter'));
        $criteria->setOrder($orders, $request->input('order'));

        return $criteria;
    }

    /**
     * Whether a search, order, filter, type or letter narrows the results.
     *
     * @return bool
     */
    public function isSearching(): bool
    {
        $narrowed = $this->search !== '' || $this->typeValue !== null || $this->letter !== '';

        return $narrowed || $this->isOrdering() || $this->isFiltering();
    }

    /**
     * Whether an ordering is applied.
     *
     * @return bool
     */
    public function isOrdering(): bool
    {
        return collect($this->order)->contains('selected', '!=', null);
    }

    /**
     * Whether a filter is applied.
     *
     * @return bool
     */
    public function isFiltering(): bool
    {
        return collect($this->filter)->contains('selected', '!=', null);
    }

    /**
     * Whether the results come from the search engine.
     *
     * @return bool
     */
    public function needsSearch(): bool
    {
        [$wheres, $whereIns] = $this->wheres();

        return $this->search !== '' || !empty($wheres) || !empty($whereIns) || !empty($this->orders());
    }

    /**
     * The orders the selected directions describe.
     *
     * @return array
     */
    public function orders(): array
    {
        $orders = [];

        foreach ($this->order as $attribute => $order) {
            if (empty($order['selected'])) {
                continue;
            }

            $orders[] = [
                'column' => str_replace(':', '.', $attribute),
                'direction' => $order['selected'],
            ];
        }

        return $orders;
    }

    /**
     * The equality and set filters the selected values describe.
     *
     * @return array
     */
    public function wheres(): array
    {
        $wheres = [];
        $whereIns = [];

        foreach ($this->filter as $attribute => $filter) {
            if ($attribute === 'library_status') {
                continue;
            }

            $selected = $filter['selected'];

            if ($selected === null) {
                continue;
            }

            $attribute = str_replace(':', '.', $attribute);

            if ($filter['type'] === 'multiselect') {
                $whereIns[$attribute] = $selected;
                continue;
            }

            $wheres[$attribute] = match ($filter['type']) {
                'date' => Carbon::createFromFormat('Y-m-d', $selected)
                    ?->setTime(0, 0)
                    ->timestamp,
                'time' => $selected . ':00',
                'double' => number_format($selected, 2, '.', ''),
                default => $selected,
            };
        }

        return [$wheres, $whereIns];
    }

    /**
     * The library statuses the results are limited to.
     *
     * @return array
     */
    public function libraryStatuses(): array
    {
        return $this->filter['library_status']['selected'] ?? [];
    }

    /**
     * The letters of the alphabetical index keyed by their label.
     *
     * @return Collection
     */
    public function letteredIndex(): Collection
    {
        $keys = range('A', 'Z');
        $values = range('a', 'z');

        return collect($keys)
            ->combine($values)
            ->prepend('.', '#');
    }

    /**
     * Keeps the letter when it is one of the alphabetical index.
     *
     * @param string $letter
     *
     * @return void
     */
    protected function setLetter(string $letter): void
    {
        $letter = strtolower($letter);

        $this->letter = preg_match('/^[a-z.]$/', $letter) ? $letter : '';
    }

    /**
     * Resolves the search type the given slug names.
     *
     * @param string $type
     *
     * @return void
     */
    protected function setType(string $type): void
    {
        if ($type === '' || $type === 'all') {
            $type = $this->defaultType;
        }

        if ($type === '') {
            return;
        }

        $typeValue = collect($this->searchTypes)
            ->search(function ($value) use ($type) {
                return str($value)->slug()->value() === $type;
            });

        if ($typeValue === false || $typeValue === 'all') {
            return;
        }

        $this->type = $type;
        $this->typeValue = $typeValue;
    }

    /**
     * Keeps the page size when it is one of the offered sizes.
     *
     * @param int $perPage
     *
     * @return void
     */
    protected function setPerPage(int $perPage): void
    {
        $this->perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 25;
    }

    /**
     * Fills the selected value of each filter from the given input.
     *
     * @param array $filters
     * @param mixed $input
     *
     * @return void
     */
    protected function setFilter(array $filters, mixed $input): void
    {
        $input = is_array($input) ? $input : [];

        foreach ($filters as $key => $filter) {
            $filter['selected'] = $this->selectedFilterValue($filter, $input[$key] ?? null);
            $this->filter[$key] = $filter;
        }
    }

    /**
     * Fills the selected direction of each order from the given input.
     *
     * @param array $orders
     * @param mixed $input
     *
     * @return void
     */
    protected function setOrder(array $orders, mixed $input): void
    {
        $input = is_array($input) ? $input : [];

        foreach ($orders as $key => $order) {
            $direction = $input[$key] ?? null;
            $order['selected'] = in_array($direction, ['asc', 'desc'], true) ? $direction : null;
            $this->order[$key] = $order;
        }
    }

    /**
     * The given input as a value the filter accepts.
     *
     * @param array $filter
     * @param mixed $value
     *
     * @return array|string|null
     */
    protected function selectedFilterValue(array $filter, mixed $value): array|string|null
    {
        if ($filter['type'] === 'multiselect') {
            $options = collect($filter['options'] ?? [])->keys()->map(fn ($option) => (string) $option);
            $values = collect(is_array($value) ? $value : [$value])
                ->filter(fn ($item) => is_scalar($item) && $options->contains((string) $item))
                ->map(fn ($item) => (string) $item)
                ->values()
                ->all();

            return empty($values) ? null : $values;
        }

        if (!is_scalar($value) || $value === '') {
            return null;
        }

        $value = (string) $value;

        $accepted = match ($filter['type']) {
            'date' => Carbon::canBeCreatedFromFormat($value, 'Y-m-d'),
            'time' => preg_match('/^\d{2}:\d{2}$/', $value) === 1,
            'number', 'double', 'duration' => is_numeric($value),
            'day' => preg_match('/^(0[1-9]|[12]\d|3[01])$/', $value) === 1,
            'month' => preg_match('/^([1-9]|1[0-2])$/', $value) === 1,
            'bool' => in_array($value, ['0', '1'], true),
            'select' => collect($filter['options'] ?? [])->keys()->contains(fn ($option) => (string) $option === $value),
            default => strlen($value) <= 255,
        };

        return $accepted ? $value : null;
    }
}
