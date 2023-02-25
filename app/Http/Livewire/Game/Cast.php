<?php

namespace App\Http\Livewire\Game;

use App\Models\Game;
use App\Models\GameCast;
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
     * Get the list of cast.
     *
     * @return GameCast[]|LengthAwarePaginator
     */
    public function getCastProperty(): array|LengthAwarePaginator
    {
        return $this->game->cast()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.game.cast');
    }
}
