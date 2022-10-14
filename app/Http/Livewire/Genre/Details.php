<?php

namespace App\Http\Livewire\Genre;

use App\Models\ExploreCategory;
use App\Models\Genre;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the genre data.
     *
     * @var Genre $genre
     */
    public Genre $genre;

    /**
     * The object containing the collection of explore category data.
     *
     * @var ExploreCategory[]|Collection $exploreCategories
     */
    public array|Collection $exploreCategories;

    /**
     * Prepare the component.
     *
     * @param Genre $genre
     * @return void
     */
    public function mount(Genre $genre): void
    {
        $this->genre = $genre;
        $this->exploreCategories = ExploreCategory::where('is_global', true)
            ->orderBy('position')
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.genre.details');
    }
}
