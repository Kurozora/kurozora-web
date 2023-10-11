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
use Illuminate\Support\Collection;
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
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Returns the chart for the specified chart kind.
     *
     * @return array|Collection
     */
    public function getChartProperty(): array|Collection
    {
        if (!$this->readyToLoad) {
            return [];
        }

        $model = match ($this->chartKind) {
            ChartKind::Anime => Anime::with(['genres', 'themes', 'media', 'mediaStat', 'translations', 'tv_rating']),
            ChartKind::Characters => Character::with(['media', 'translations']),
            ChartKind::Episodes => Episode::with([
                'anime' => function ($query) {
                    $query->with(['media', 'translations']);
                },
                'media',
                'season' => function ($query) {
                    $query->with(['translations']);
                },
                'translations'
            ]),
            ChartKind::Games => Game::with(['genres', 'themes', 'media', 'mediaStat', 'translations', 'tv_rating']),
            ChartKind::Manga => Manga::with(['genres', 'themes', 'media', 'mediaStat', 'translations', 'tv_rating']),
            ChartKind::People => Person::with(['media']),
            ChartKind::Songs => Song::with(['media']),
            ChartKind::Studios => Studio::with(['media'])
        };

        return $model->where('rank_total', '!=', 0)
            ->orderBy('rank_total')
            ->limit(15)
            ->get();
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
