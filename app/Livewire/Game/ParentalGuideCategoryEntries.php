<?php

namespace App\Livewire\Game;

use App\Models\Game;
use App\Traits\Livewire\ParentalGuideCategoryListing;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ParentalGuideCategoryEntries extends Component
{
    use ParentalGuideCategoryListing;

    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * Prepare the component.
     *
     * @param Game   $game
     * @param string $category
     *
     * @return void
     */
    public function mount(Game $game, string $category): void
    {
        $this->game = $game->load(['media', 'translation']);
        $this->resolveCategoryFromSlug($category);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.game.parental-guide-category-entries');
    }

    /**
     * @inheritDoc
     */
    protected function listingTargetModel(): Model
    {
        return $this->game;
    }
}
