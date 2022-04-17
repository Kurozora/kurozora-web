<?php

namespace App\Http\Livewire\Anime;

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
     * Redirect the user to a random anime.
     *
     * @return void
     */
    public function randomAnime()
    {
        $this->redirectRoute('anime.details', Anime::inRandomOrder()->first());
    }

    /**
     * The computed anime property.
     *
     * @return LengthAwarePaginator
     */
    public function getAnimesProperty(): LengthAwarePaginator
    {
        // Search
        if (!empty($this->filter['search'])) {
            $animes = Anime::kSearch($this->filter['search']);
        } else {
            $animes = Anime::query();
        }

//        // Order
//        if (!empty($this->filter['order_type'])) {
//            $animes = $animes->orderByTranslation('title', $this->filter['order_type']);
//        }

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
        return view('livewire.anime.index');
    }
}
