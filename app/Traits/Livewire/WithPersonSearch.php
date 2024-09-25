<?php

namespace App\Traits\Livewire;

use App\Models\Person;
use Carbon\Month;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

trait WithPersonSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Person::class;

    /**
     * The column used for the letter index query.
     *
     * @return string
     */
    protected function letterIndexColumn(): string
    {
        return 'first_name';
    }

    /**
     * The column used for the letter index query.
     *
     * @return string
     */
    protected function typeColumn(): string
    {
        return 'birthdate';
    }

    /**
     * Redirect the user to a random person.
     *
     * @return void
     */
    public function randomPerson(): void
    {
        $person = Person::inRandomOrder()->first();
        $this->redirectRoute('people.details', $person);
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
        $keys = collect($query->getQuery()->wheres)
            ->where('column', '=', $this->typeColumn())
            ->keys();
        unset($query->getQuery()->bindings['where'][$keys->first()]);
        unset($query->getQuery()->wheres[$keys->first()]);

        return $query->with(['media'])
            ->when(!empty($this->typeValue), function (EloquentBuilder $query) {
                $query->whereMonth($this->typeColumn(), '=', $this->typeValue);
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
        unset($query->wheres[$this->typeColumn()]);

        return $query->query(function (EloquentBuilder $query) {
            $query->with(['media']);
        })
            ->when(!empty($this->typeValue), function (ScoutBuilder $query) {
                $query->where('birth_month', $this->typeValue);
            });
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return array
     */
    public function setOrderableAttributes(): array
    {
        return Person::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        return Person::webSearchFilters();
    }

    /**
     * Set the search types of the model.
     *
     * @return array
     */
    public function setSearchTypes(): array
    {
        // Since array keys start from 0, even when casting the numbers to a string,
        // we prepend a temp value to the array and then unset it, preserving the
        // correct keys.
        $months = collect(Month::cases())->flatMap(function ($month) {
            return [
                $month->value => $month->name
            ];
        })
            ->unshift('temp')
            ->prepend(__('All'), 'all');
        $months->offsetUnset(0);
        return $months->toArray();
    }
}
