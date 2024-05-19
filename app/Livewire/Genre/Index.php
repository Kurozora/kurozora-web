<?php

namespace App\Livewire\Genre;

use App\Models\Genre;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    /**
     * The object containing the collection of genres.
     *
     * @var Collection|Genre[] $genres
     */
    public Collection|array $genres;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount()
    {
        $this->genres = Genre::orderBy('name')
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.genre.index');
    }
}
