<?php

namespace App\Http\Livewire\Season;

use App\Models\Season;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Episodes extends Component
{
    use WithPagination;

    /**
     * The object containing the anime data.
     *
     * @var Season $season
     */
    public Season $season;

    /**
     * Prepare the component.
     *
     * @param Season $season
     *
     * @return void
     */
    public function mount(Season $season)
    {
        $this->season = $season;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.season.episodes', [
            'episodes' => $this->season->episodes()->paginate(25)
        ]);
    }
}
