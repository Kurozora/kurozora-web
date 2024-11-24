<?php

namespace App\Traits\Livewire;

use App\Models\Episode;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

trait WithEpisodeSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Episode::class;

    /**
     * The column used for the letter index query.
     *
     * @return string
     */
    protected function letterIndexColumn(): string
    {
        return 'title';
    }

    /**
     * The column used for the letter index query.
     *
     * @return string
     */
    protected function typeColumn(): string
    {
        return 'is_filler';
    }

    /**
     * Redirect the user to a random anime.
     *
     * @return void
     */
    public function randomEpisode(): void
    {
        $episode = Episode::where('season_id', $this->season->id)
            ->inRandomOrder()
            ->first();
        $this->redirectRoute('episodes.details', $episode);
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
        return $query->withoutGlobalScopes()
            ->with([
                'anime' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->with(['media', 'translation']);
                },
                'media',
                'season' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->with(['translation']);
                },
                'translation',
            ])
            ->when(auth()->user(), function ($query, $user) {
                return $query->withExists([
                    'user_watched_episodes as isWatched' => function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    }
                ]);
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
            $query->withoutGlobalScopes()
                ->with([
                    'anime' => function ($query) {
                        $query->withoutGlobalScopes()
                            ->with(['media', 'translation']);
                    },
                    'media',
                    'season' => function ($query) {
                        $query->withoutGlobalScopes()
                            ->with(['translation']);
                    },
                    'translation',
                ])
                ->when(auth()->user(), function ($query, $user) {
                    return $query->withExists([
                        'user_watched_episodes as isWatched' => function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        }
                    ]);
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
        return Episode::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        return Episode::webSearchFilters();
    }

    /**
     * Set the search types of the model.
     *
     * @return array
     */
    public function setSearchTypes(): array
    {
        return [
            'all' => __('All'),
            false => __('Canon'),
            true => __('Filler'),
        ];
    }
}
