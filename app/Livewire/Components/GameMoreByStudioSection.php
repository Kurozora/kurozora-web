<?php

namespace App\Livewire\Components;

use App\Models\Game;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class GameMoreByStudioSection extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game
     */
    public Game $game;

    /**
     * The object containing the studio data.
     *
     * @var Studio
     */
    public Studio $studio;

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
     * @param Studio $studio
     *
     * @return void
     */
    public function mount(Game $game, Studio $studio): void
    {
        $translation = $game->translation;
        $this->game = $game->withoutRelations()
            ->setRelation('translation', $translation);
        $this->studio = $studio;
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
     * Loads the more by studio section.
     *
     * @return Collection
     */
    public function getMoreByStudioProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->studio->games()
            ->when($this->studio->tv_rating_id > config('app.tv_rating'), function ($query) {
                $query->withoutGlobalScopes();
            })
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->where('model_id', '!=', $this->game->id)
            ->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.game-more-by-studio-section');
    }
}
