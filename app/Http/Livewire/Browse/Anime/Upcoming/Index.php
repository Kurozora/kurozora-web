<?php

namespace App\Http\Livewire\Browse\Anime\Upcoming;

use App\Models\Anime;
use App\Traits\Livewire\WithAnimeSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Index extends Component
{
    use WithAnimeSearch;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

    /**
     * Redirect the user to a random anime.
     *
     * @return void
     */
    public function randomAnime(): void
    {
        $anime = Anime::search()->where('first_aired', ['>=', yesterday()->timestamp])
            ->get()
            ->random(1)
            ->first();
        $this->redirectRoute('anime.details', $anime);
    }

    /**
     * Build an 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->whereDate('first_aired', '>=', yesterday());
    }

    /**
     * Build an 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->where('first_aired', ['>=', yesterday()->timestamp]);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.anime.upcoming.index');
    }
}
