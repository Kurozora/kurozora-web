<?php

namespace App\Http\Livewire\Browse\Manga\Continuing;

use App\Models\Manga;
use App\Traits\Livewire\WithMangaSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Index extends Component
{
    use WithMangaSearch;

    /**
     * Redirect the user to a random manga.
     *
     * @return void
     */
    public function randomManga(): void
    {
        $manga = Manga::search()
            ->where('publication_season', ['!=', season_of_year()->value])
            ->where('started_at', ['<=', now()])
            ->where('started_at', ['!=', now()->year])
            ->where('status_id', ['=', 3])
            ->get()
            ->random(1)
            ->first();

        $this->redirectRoute('manga.details', $manga);
    }

    /**
     * Build an 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query
            ->where('publication_season', '!=', season_of_year()->value)
            ->whereDate('started_at', '<=', now())
            ->where('status_id', '=', 8)
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
        return $query->where('publication_season', ['!=', season_of_year()->value])
            ->where('started_at', ['<=', now()->timestamp])
            ->where('status_id', ['=', 8]);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.manga.continuing.index');
    }
}
