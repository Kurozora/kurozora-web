<?php

namespace App\Traits\Livewire;

use App\Models\Anime;

trait WithAnimeSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Anime::class;

    /**
     * Redirect the user to a random anime.
     *
     * @return void
     */
    public function randomAnime(): void
    {
        $anime = Anime::inRandomOrder()->first();
        $this->redirectRoute('anime.details', $anime);
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return void
     */
    public function setOrderableAttributes(): void
    {
        $this->order = Anime::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return void
     */
    public function setFilterableAttributes(): void
    {
        $this->filter = Anime::webSearchFilters();
    }
}
