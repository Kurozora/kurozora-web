<?php

namespace App\Traits\Livewire;

use App\Models\Manga;

trait WithMangaSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Manga::class;

    /**
     * Redirect the user to a random manga.
     *
     * @return void
     */
    public function randomManga(): void
    {
        $manga = Manga::inRandomOrder()->first();
        $this->redirectRoute('manga.details', $manga);
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return void
     */
    public function setOrderableAttributes(): void
    {
        $this->order = Manga::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return void
     */
    public function setFilterableAttributes(): void
    {
        $this->filter = Manga::webSearchFilters();
    }
}
