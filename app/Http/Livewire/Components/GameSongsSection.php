<?php

namespace App\Http\Livewire\Components;

use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GameSongsSection extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * The array containing the game songs data.
     *
     * @var array $mediaSongs
     */
    public array $mediaSongs = [];

    /**
     * The number of songs the game has.
     *
     * @var int $mediaSongsCount
     */
    public int $mediaSongsCount;

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
        $this->mediaSongsCount = $game->getMediaSongs()->count();
    }

    /**
     * Loads the media songs section.
     *
     * @return void
     */
    public function loadMediaSongs(): void
    {
        $this->mediaSongs = $this->game->getMediaSongs(Game::MAXIMUM_RELATIONSHIPS_LIMIT)->items() ?? [];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.game-songs-section');
    }
}
