<?php

namespace App\Http\Livewire\Browse\Anime\Continuing;

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
     * Redirect the user to a random anime.
     *
     * @return void
     */
    public function randomAnime(): void
    {
        $anime = Anime::search()
            ->where('air_season', ['!=', season_of_year()->value])
            ->where('started_at', ['<=', now()])
            ->where('started_at', ['!=', now()->year])
            ->where('status_id', ['=', 3])
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
        return $query->where('air_season', '!=', season_of_year()->value)
            ->whereDate('started_at', '<=', now())
            ->where('status_id', '=', 3)
            ->orderBy('started_at', 'desc');
    }

    /**
     * Build an 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->where('air_season', ['!=', season_of_year()->value])
            ->where('started_at', ['<=', now()->timestamp])
            ->where('status_id', ['=', 3]);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.anime.continuing.index');
    }
}
