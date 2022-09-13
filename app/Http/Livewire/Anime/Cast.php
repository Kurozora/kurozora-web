<?php

namespace App\Http\Livewire\Anime;

use App\Models\Anime;
use App\Models\AnimeCast;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Cast extends Component
{
    use WithPagination;

    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * Prepare the component.
     *
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime): void
    {
        $this->anime = $anime;
    }

    /**
     * Get the list of cast.
     *
     * @return AnimeCast[]|LengthAwarePaginator
     */
    public function getCastProperty(): array|LengthAwarePaginator
    {
        return $this->anime->cast()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.cast');
    }
}
