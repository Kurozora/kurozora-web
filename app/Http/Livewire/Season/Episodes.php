<?php

namespace App\Http\Livewire\Season;

use App\Events\SeasonViewed;
use App\Models\Season;
use App\Traits\Livewire\WithEpisodeSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Episodes extends Component
{
    use WithEpisodeSearch;

    /**
     * The object containing the season data.
     *
     * @var Season $season
     */
    public Season $season;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'update-season' => '$refresh'
    ];

    /**
     * Prepare the component.
     *
     * @param Season $season
     *
     * @return void
     */
    public function mount(Season $season): void
    {
        // Call the SeasonViewed event
        SeasonViewed::dispatch($season);

        $this->season = $season;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.season.episodes');
    }
}
