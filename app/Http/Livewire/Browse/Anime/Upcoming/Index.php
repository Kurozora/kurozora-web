<?php

namespace App\Http\Livewire\Browse\Anime\Upcoming;

use App\Models\Anime;
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
     * The component's filters.
     *
     * @var array $filter
     */
    public array $filter = [
        'search' => '',
        'order_type' => '',
        'per_page' => 25,
    ];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount() {}

    /**
     * The computed upcoming anime property.
     *
     * @return LengthAwarePaginator
     */
    public function getAnimesProperty(): LengthAwarePaginator
    {
        $animes = Anime::whereDate('first_aired', '>', yesterday());

        // Search
        if (!empty($this->filter['search'])) {
            $animes = $animes->search($this->filter['search'], null, true, true);
        }

        // Order
        if (!empty($this->filter['order_type'])) {
            $animes = $animes->orderByTranslation('title', $this->filter['order_type']);
        } else {
            $animes = $animes->orderBy('first_aired');
        }

        // Paginate
        return $animes->paginate($this->filter['per_page'] ?? 25);
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