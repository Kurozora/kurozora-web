<?php

namespace App\Traits\Livewire;

use App\Enums\DayOfWeek;
use App\Enums\SeasonOfYear;
use App\Models\Game;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;

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
            'published_at' => [
                'title' => __('First Published'),
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
        $this->filter = [
            'published_at' => [
                'title' => __('First Published'),
                'type' => 'date',
                'selected' => null,
            ],
            'duration' => [
                'title' => __('Duration (seconds)'),
                'type' => 'duration',
                'selected' => null,
            ],
            'tv_rating_id' => [
                'title' => __('TV Rating'),
                'type' => 'select',
                'options' => TvRating::all()->pluck('name', 'id'),
                'selected' => null,
            ],
            'media_type_id' => [
                'title' => __('Media Type'),
                'type' => 'select',
                'options' => MediaType::where('type', 'game')->pluck('name', 'id'),
                'selected' => null,
            ],
            'source_id' => [
                'title' => __('Source'),
                'type' => 'select',
                'options' => Source::all()->pluck('name', 'id'),
                'selected' => null,
            ],
            'status_id' => [
                'title' => __('Publication Status'),
                'type' => 'select',
                'options' => Status::where('type', 'game')->pluck('name', 'id'),
                'selected' => null,
            ],
            'publication_day' => [
                'title' => __('Publication Day'),
                'type' => 'select',
                'options' => DayOfWeek::asSelectArray(),
                'selected' => null,
            ],
            'publication_season' => [
                'title' => __('Publication Season'),
                'type' => 'select',
                'options' => SeasonOfYear::asSelectArray(),
                'selected' => null,
            ],
            'edition_count' => [
                'title' => __('Edition Count'),
                'type' => 'number',
                'selected' => null,
            ],
        ];

        if (auth()->check()) {
            if (settings('tv_rating') >= 4) {
                $this->filter['is_nsfw'] = [
                    'title' => __('NSFW'),
                    'type' => 'bool',
                    'options' => [
                        __('Shown'),
                        __('Hidden'),
                    ],
                    'selected' => null,
                ];
            }
        }
    }
}
