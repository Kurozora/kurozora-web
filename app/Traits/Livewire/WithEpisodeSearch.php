<?php

namespace App\Traits\Livewire;

use App\Models\Episode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
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
     * Build an 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where('season_id', $this->season->id);
    }

    /**
     * Build an 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->where('season_id', $this->season->id);
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return void
     */
    public function setOrderableAttributes(): void
    {
        $this->order = [
            'title' => [
                'title' => __('Title'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'number' => [
                'title' => __('Number (Season)'),
                'options' => [
                    'Default' => null,
                    '0-9' => 'asc',
                    '9-0' => 'desc',
                ],
                'selected' => null,
            ],
            'number_total' => [
                'title' => __('Number (Series)'),
                'options' => [
                    'Default' => null,
                    '0-9' => 'asc',
                    '9-0' => 'desc',
                ],
                'selected' => null,
            ],
            'started_at' => [
                'title' => __('First Aired'),
                'options' => [
                    'Default' => null,
                    'Newest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'duration' => [
                'title' => __('Duration'),
                'options' => [
                    'Default' => null,
                    'Shortest' => 'asc',
                    'Longest' => 'desc',
                ],
                'selected' => null,
            ],
        ];
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return void
     */
    public function setFilterableAttributes(): void
    {
        $this->filter = Episode::webSearchFilters();
    }
}
