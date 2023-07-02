<?php

namespace App\Http\Livewire\Components\Chart;

use App\Enums\ChartKind;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;

class Section extends Component
{
    /**
     * The chart kind.
     *
     * @var string $chartKind
     */
    public string $chartKind;

    /**
     * Whether the component is initialized.
     *
     * @var bool $isInit
     */
    public bool $isInit = false;

    /**
     * Prepare the component.
     *
     * @param string $chartKind
     * @return void
     */
    public function mount(string $chartKind): void
    {
        $this->chartKind = $chartKind;
    }

    /**
     * Loads the explore category section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->isInit = true;
    }

    /**
     * Returns the chart for the specified chart kind.
     *
     * @return LengthAwarePaginator|null
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
            ->paginate();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.chart.section');
    }
}
