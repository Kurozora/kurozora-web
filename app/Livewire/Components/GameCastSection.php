<?php

namespace App\Livewire\Components;

use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class GameCastSection extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Game $game
     *
     * @return void
     */
    public function mount(Game $game): void
    {
        $translation = $game->translation;
        $this->game = $game->withoutRelations()
            ->setRelation('translation', $translation);
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Loads the game cast section.
     *
     * @return Collection
     */
    public function getGameCastProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->game->cast()
            ->with([
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'character' => function ($query) {
                    $query->with(['media', 'translation']);
                },
                'castRole'
            ])
            ->limit(Game::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.game-cast-section');
    }
}
