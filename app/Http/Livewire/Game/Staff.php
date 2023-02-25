<?php

namespace App\Http\Livewire\Game;

use App\Models\Game;
use App\Models\MediaStaff;
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
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * Prepare the component.
     *
     * @param Game $game
     *
     * @return void
     */
    public function mount(Game $game): void
    {
        $this->game = $game;
    }

    /**
     * Get the list of media staff.
     *
     * @return MediaStaff[]|LengthAwarePaginator
     */
    public function getMediaStaffProperty(): array|LengthAwarePaginator
    {
        return $this->game->mediaStaff()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.game.staff');
    }
}
