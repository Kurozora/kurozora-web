<?php

namespace App\Traits\Livewire;

use App\Models\Anime;
use App\Models\MediaType;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

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
     * Build a 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     *
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
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
        return $query->query(function (EloquentBuilder $query) {
            $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                });
        });
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return array
     */
    public function setOrderableAttributes(): array
    {
        return Anime::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        return Anime::webSearchFilters();
    }

    /**
     * Set the search types of the model.
     *
     * @return array
     */
    public function setSearchTypes(): array
    {
        return MediaType::where('type', '=', 'anime')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(__('All'), 'all')
            ->toArray();
    }
}
