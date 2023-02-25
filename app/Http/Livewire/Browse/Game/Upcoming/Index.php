<?php

namespace App\Http\Livewire\Browse\Game\Upcoming;

use App\Models\Game;
use App\Traits\Livewire\WithGameSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Index extends Component
{
    use WithGameSearch;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

    /**
     * Redirect the user to a random game.
     *
     * @return void
     */
    public function randomGame(): void
    {
        $game = Game::search()->where('published_at', ['>=', yesterday()->timestamp])
            ->get()
            ->random(1)
            ->first();
        $this->redirectRoute('games.details', $game);
    }

    /**
     * Build an 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->whereDate('published_at', '>=', yesterday());
    }

    /**
     * Build an 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->where('published_at', ['>=', yesterday()->timestamp]);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.game.upcoming.index');
    }
}
