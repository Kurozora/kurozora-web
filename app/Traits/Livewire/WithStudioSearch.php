<?php

namespace App\Traits\Livewire;

use App\Models\Studio;

trait WithStudioSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Studio::class;

    /**
     * The column used for the letter index query.
     *
     * @return string
     */
    protected function letterIndexColumn(): string
    {
        return 'name';
    }

    /**
     * Redirect the user to a random studio.
     *
     * @return void
     */
    public function randomStudio(): void
    {
        $studio = Studio::inRandomOrder()->first();
        $this->redirectRoute('studios.details', $studio);
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return void
     */
    public function setOrderableAttributes(): void
    {
        $this->order = Studio::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return void
     */
    public function setFilterableAttributes(): void
    {
        $this->filter = Studio::webSearchFilters();
    }
}
