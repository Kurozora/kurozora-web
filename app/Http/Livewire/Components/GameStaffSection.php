<?php

namespace App\Http\Livewire\Components;

use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GameStaffSection extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * The number of staff the game has.
     *
     * @var int $staffCount
     */
    public int $staffCount;

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
        $this->staffCount = $game->mediaStaff()->count();
    }

    /**
     * Loads the media staff section.
     *
     * @return array
     */
    public function getMediaStaffProperty(): array
    {
        return $this->game->getMediaStaff(Game::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.game-staff-section');
    }
}
