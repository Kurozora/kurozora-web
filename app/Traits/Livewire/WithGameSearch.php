<?php

namespace App\Traits\Livewire;

use App\Models\Game;
use App\Models\MediaType;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

trait WithGameSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Game::class;

    /**
     * Redirect the user to a random game.
     *
     * @return void
     */
    public function randomGame(): void
    {
        $game = Game::inRandomOrder()->first();
        $this->redirectRoute('games.details', $game);
    }

    /**
     * Build a 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
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
        return Game::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        return Game::webSearchFilters();
    }

    /**
     * Set the search types of the model.
     *
     * @return array
     */
    public function setSearchTypes(): array
    {
        return MediaType::where('type', '=', 'game')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(__('All'), 'all')
            ->toArray();
    }
}
