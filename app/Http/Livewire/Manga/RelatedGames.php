<?php

namespace App\Http\Livewire\Manga;

use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class RelatedGames extends Component
{
    use WithPagination;

    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * Prepare the component.
     *
     * @param Manga $manga
     *
     * @return void
     */
    public function mount(Manga $manga): void
    {
        $this->manga = $manga;
    }

    /**
     * The object containing the related game.
     *
     * @return LengthAwarePaginator
     */
    public function getGameRelationsProperty(): LengthAwarePaginator
    {
        return $this->manga->gameRelations()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.manga.related-games');
    }
}