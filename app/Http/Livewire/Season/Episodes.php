<?php

namespace App\Http\Livewire\Season;

use App\Models\Episode;
use App\Models\Season;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Episodes extends Component
{
    use WithPagination;

    /**
     * The object containing the season data.
     *
     * @var Season $season
     */
    public Season $season;

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
     * @param Season $season
     *
     * @return void
     */
    public function mount(Season $season): void
    {
        $this->season = $season;
        $this->setFilterableAttributes();
        $this->setOrderableAttributes();
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

    function getEpisodesProperty(): LengthAwarePaginator
    {
        // Search
        $episodes = Episode::search($this->search);
        $episodes = $episodes->where('season_id', $this->season->id);

        // Order
        foreach ($this->order as $attribute => $order) {
            $selected = $order['selected'];
            if (!empty($selected)) {
                $episodes = $episodes->orderBy($attribute, $selected);
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
                        $episodes = $episodes->where($attribute, $date);
                        break;
                    case 'time':
                        $time = $selected . ':00';
                        $episodes = $episodes->where($attribute, $time);
                        break;
                    default:
                        $episodes = $episodes->where($attribute, $selected);
                }
            }
        }

        // Paginate
        return $episodes->paginate($this->perPage);
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
            'first_aired' => [
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
        $this->filter = [
            'number' => [
                'title' => __('Number (Season)'),
                'type' => 'number',
                'selected' => null,
            ],
            'number_total' => [
                'title' => __('Number (Series)'),
                'type' => 'number',
                'selected' => null,
            ],
            'first_aired' => [
                'title' => __('First Aired'),
                'type' => 'date',
                'selected' => null,
            ],
            'duration' => [
                'title' => __('Duration (seconds)'),
                'type' => 'duration',
                'selected' => null,
            ],
            'is_filler' => [
                'title' => __('Fillers'),
                'type' => 'bool',
                'options' => [
                    __('Shown'),
                    __('Hidden'),
                ],
                'selected' => null,
            ],
        ];
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
        return view('livewire.season.episodes');
    }
}
