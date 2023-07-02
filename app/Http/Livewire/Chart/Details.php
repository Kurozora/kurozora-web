<?php

namespace App\Http\Livewire\Chart;

use App\Enums\ChartKind;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Traits\Livewire\WithPagination;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Details extends Component
{
    use WithPagination;

    /**
     * The kind of the selected chart.
     *
     * @var string $chartKind
     */
    public $chartKind;

    /**
     * Prepare the component.
     *
     * @param string $chart
     * @return void
     */
    public function mount(string $chart): void
    {
        $this->chartKind = $chart;
    }

    /**
     * The computed chart property.
     *
     * @return ?LengthAwarePaginator
     */
    public function getChartProperty(): ?LengthAwarePaginator
    {
        $model = match ($this->chartKind) {
            ChartKind::Anime => Anime::class,
            ChartKind::Characters => Character::class,
            ChartKind::Episodes => Episode::class,
            ChartKind::Games => Game::class,
            ChartKind::Manga => Manga::class,
            ChartKind::People => Person::class,
            ChartKind::Songs => Song::class,
            ChartKind::Studios => Studio::class
        };

        return $model::orderBy('rank_total')
                ->where('rank_total', '!=', 0)
                ->paginate($this->perPage);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.chart.details');
    }
}
