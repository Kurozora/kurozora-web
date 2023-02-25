<?php

namespace App\Http\Livewire\Studio;

use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Games extends Component
{
    use WithPagination;

    /**
     * The object containing the studio data.
     *
     * @var Studio $studio
     */
    public Studio $studio;

    /**
     * Prepare the component.
     *
     * @param Studio $studio
     *
     * @return void
     */
    public function mount(Studio $studio): void
    {
        $this->studio = $studio;
    }

    /**
     * The studio's games.
     *
     * @return LengthAwarePaginator
     */
    public function getGamesProperty(): LengthAwarePaginator
    {
        return $this->studio->games()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.studio.games');
    }
}
