<?php

namespace App\Livewire\Genre;

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
     * Determines whether to load the page.
     *
     * @var bool $readyToLoad
     */
    public $readyToLoad = false;

    /**
     * The object containing the genre data.
     *
     * @var Genre $genre
     */
    public Genre $genre;

    /**
     * Prepare the component.
     *
     * @param Genre $genre
     * @return void
     */
    public function mount(Genre $genre): void
    {
        $this->genre = $genre->load(['media']);
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
     * The object containing the collection of explore category data.
     *
     * @return array|Collection
     */
    function getExploreCategoriesProperty(): array|Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return ExploreCategory::where('is_global', true)
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
