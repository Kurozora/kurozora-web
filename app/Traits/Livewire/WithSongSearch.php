<?php

namespace App\Traits\Livewire;

use App\Models\Song;

trait WithSongSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Song::class;

    /**
     * Redirect the user to a random studio.
     *
     * @return void
     */
    public function randomSong(): void
    {
        $studio = Song::inRandomOrder()->first();
        $this->redirectRoute('songs.details', $studio);
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return array
     */
    public function setOrderableAttributes(): array
    {
        return Song::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        return Song::webSearchFilters();
    }
}
