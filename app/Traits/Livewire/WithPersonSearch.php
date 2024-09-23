<?php

namespace App\Traits\Livewire;

use App\Models\Person;

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
}
