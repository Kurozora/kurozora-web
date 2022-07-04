<?php

namespace App\Http\Livewire\Browse\Anime\Upcoming;

use App\Enums\DayOfWeek;
use App\Enums\SeasonOfYear;
use App\Models\Anime;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * The search string.
     *
     * @var string $search
     */
    public string $search = '';

    /**
     * The number of results per page.
     *
     * @var int $perPage
     */
    public int $perPage = 25;

    /**
     * The component's filter attributes.
     *
     * @var array $filter
     */
    public array $filter = [];

    /**
     * The component's order attributes.
     *
     * @var array $order
     */
    public array $order = [];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->setFilterableAttributes();
        $this->setOrderableAttributes();
    }

    /**
     * Redirect the user to a random anime.
     *
     * @return void
     */
    public function randomAnime(): void
    {
        $anime = Anime::search()->where('first_aired', ['>', yesterday()->timestamp])
            ->get()
            ->random(1)
            ->first();
        $this->redirectRoute('anime.details', $anime);
    }

    /**
     * The computed upcoming anime property.
     *
     * @return LengthAwarePaginator
     */
    public function getAnimesProperty(): LengthAwarePaginator
    {
        // Search
        $animes = Anime::search($this->search);
        $animes->where('first_aired', ['>', yesterday()->timestamp]);

        // Order
        foreach ($this->order as $attribute => $order) {
            $selected = $order['selected'];
            if (!empty($selected)) {
                $animes = $animes->orderBy($attribute, $selected);
            }
        }

        // Filter
        foreach ($this->filter as $attribute => $filter) {
            $selected = $filter['selected'];
            $type = $filter['type'];

            if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                switch ($type) {
                    case 'date':
                        $date = Carbon::createFromFormat('Y-m-d', $selected)
                            ->setTime(0, 0)
                            ->timestamp;
                        $animes = $animes->where($attribute, $date);
                        break;
                    case 'time':
                        $time = $selected . ':00';
                        $animes = $animes->where($attribute, $time);
                        break;
                    default:
                        $animes = $animes->where($attribute, $selected);
                }
            }
        }

        // Paginate
        return $animes->paginate($this->perPage);
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

        if (Auth::check()) {
            if (settings('tv_rating') >= 4) {
                $this->filter['is_nsfw'] = [
                    'title' => __('NSFW'),
                    'type' => 'bool',
                    'selected' => null,
                ];
            }
        }
    }

    /**
     * Reset order to default values.
     *
     * @return void
     */
    public function resetOrder(): void
    {
        $this->order = array_map(function ($order) {
            $order['selected'] = null;
            return $order;
        }, $this->order);
    }

    /**
     * Reset filter to default values.
     *
     * @return void
     */
    public function resetFilter(): void
    {
        $this->filter = array_map(function ($filter) {
            $filter['selected'] = null;
            return $filter;
        }, $this->filter);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.anime.upcoming.index');
    }
}
