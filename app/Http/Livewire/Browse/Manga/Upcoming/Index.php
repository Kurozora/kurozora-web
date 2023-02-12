<?php

namespace App\Http\Livewire\Browse\Manga\Upcoming;

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
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

    /**
     * Redirect the user to a random manga.
     *
     * @return void
     */
    public function randomManga(): void
    {
        $manga = Manga::search()->where('started_at', ['>=', yesterday()->timestamp])
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
        return $query->whereDate('started_at', '>=', yesterday());
    }

    /**
     * Build an 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->where('started_at', ['>=', yesterday()->timestamp]);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.manga.upcoming.index');
    }
}
