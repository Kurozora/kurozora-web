<?php

namespace App\Livewire\Game;

use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class RelatedGames extends Component
{
    use WithPagination;

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
        $this->game = $game->load(['media']);
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * The object containing the related game.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getGameRelationsProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->game->gameRelations()
            ->with([
                'related' => function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        });
                },
                'relation'
            ])
            ->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.game.related-games');
    }
}
