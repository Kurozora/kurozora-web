<?php

namespace App\Traits\Livewire;

use App\Enums\DayOfWeek;
use App\Enums\SeasonOfYear;
use App\Models\Manga;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;

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
            'started_at' => [
                'title' => __('First Published'),
                'options' => [
                    'Default' => null,
                    'Newest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'ended_at' => [
                'title' => __('Last Published'),
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
            'started_at' => [
                'title' => __('First Published'),
                'type' => 'date',
                'selected' => null,
            ],
            'ended_at' => [
                'title' => __('Last Published'),
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
                'options' => MediaType::where('type', 'manga')->pluck('name', 'id'),
                'selected' => null,
            ],
            'source_id' => [
                'title' => __('Source'),
                'type' => 'select',
                'options' => Source::all()->pluck('name', 'id'),
                'selected' => null,
            ],
            'status_id' => [
                'title' => __('Airing Status'),
                'type' => 'select',
                'options' => Status::where('type', 'manga')->pluck('name', 'id'),
                'selected' => null,
            ],
            'publication_time' => [
                'title' => __('Publication Time'),
                'type' => 'time',
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
            'volume_count' => [
                'title' => __('Volume Count'),
                'type' => 'number',
                'selected' => null,
            ],
            'chapter_count' => [
                'title' => __('Chapter Count'),
                'type' => 'number',
                'selected' => null,
            ],
            'page_count' => [
                'title' => __('Page Count'),
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
