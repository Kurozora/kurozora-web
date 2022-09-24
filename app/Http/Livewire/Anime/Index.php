<?php

namespace App\Http\Livewire\Anime;

use App\Enums\DayOfWeek;
use App\Enums\SeasonOfYear;
use App\Models\Anime;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;
use App\Traits\Livewire\WithSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Anime::class;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

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
            'first_aired' => [
                'title' => __('First Aired'),
                'options' => [
                    'Default' => null,
                    'Newest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'last_aired' => [
                'title' => __('Last Aired'),
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
            'first_aired' => [
                'title' => __('First Aired'),
                'type' => 'date',
                'selected' => null,
            ],
            'last_aired' => [
                'title' => __('Last Aired'),
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
                'options' => MediaType::where('type', 'anime')->pluck('name', 'id'),
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
                'options' => Status::where('type', 'anime')->pluck('name', 'id'),
                'selected' => null,
            ],
            'air_time' => [
                'title' => __('Air Time'),
                'type' => 'time',
                'selected' => null,
            ],
            'air_day' => [
                'title' => __('Air Day'),
                'type' => 'select',
                'options' => DayOfWeek::asSelectArray(),
                'selected' => null,
            ],
            'air_season' => [
                'title' => __('Air Season'),
                'type' => 'select',
                'options' => SeasonOfYear::asSelectArray(),
                'selected' => null,
            ]
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

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.index');
    }
}
