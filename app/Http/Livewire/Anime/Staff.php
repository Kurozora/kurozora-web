<?php

namespace App\Http\Livewire\Anime;

use App\Models\Anime;
use App\Models\AnimeStaff;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Staff extends Component
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
     * Get the list of staff.
     *
     * @return AnimeStaff[]|LengthAwarePaginator
     */
    public function getStaffProperty(): array|LengthAwarePaginator
    {
        return $this->anime->staff()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.staff');
    }
}
