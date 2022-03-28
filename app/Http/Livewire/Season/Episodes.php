<?php

namespace App\Http\Livewire\Season;

use App\Models\Season;
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
     * The component's filters.
     *
     * @var array $filter
     */
    public array $filter = [
        'search' => '',
        'order_type' => '',
        'per_page' => 25,
        'hide_fillers' => false,
    ];

    /**
     * Prepare the component.
     *
     * @param Season $season
     *
     * @return void
     */
    public function mount(Season $season)
    {
        $this->season = $season;
    }

    protected function getEpisodesProperty(): LengthAwarePaginator
    {
        $episodes = $this->season->episodes();

        // Search
        if (!empty($this->filter['search'])) {
            $episodes = $episodes->whereTranslationLike('title', '%' . $this->filter['search'] . '%');
        }

        // Order
        if (!empty($this->filter['order_type'])) {
            $episodes = $episodes->orderByTranslation('title', $this->filter['order_type']);
        }

        // Fillers
        if ($this->filter['hide_fillers']) {
            $episodes->where('is_filler', '!=', $this->filter['hide_fillers']);
        }

        // Paginate
        return $episodes->paginate($this->filter['per_page'] ?? 25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.season.episodes', [
            'episodes' => $this->getEpisodesProperty()
        ]);
    }
}
