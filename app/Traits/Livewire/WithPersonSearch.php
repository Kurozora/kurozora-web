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
     * @return void
     */
    public function setOrderableAttributes(): void
    {
        $this->order = Person::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return void
     */
    public function setFilterableAttributes(): void
    {
        $this->filter = Person::webSearchFilters();
    }
}
