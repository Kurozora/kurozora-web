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
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * The computed chart property.
     *
     * @return array|LengthAwarePaginator
     */
    public function getChartProperty(): array|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return [];
        }

        $model = match ($this->chartKind) {
            ChartKind::Anime => Anime::with(['genres', 'themes', 'media', 'mediaStat', 'translations', 'tv_rating']),
            ChartKind::Characters => Character::with(['media', 'translations']),
            ChartKind::Episodes => Episode::with(['media', 'season' => function ($query) {
                $query->with(['anime.translations', 'translations']);
            }, 'translations']),
            ChartKind::Games => Game::with(['genres', 'themes', 'media', 'mediaStat', 'translations', 'tv_rating']),
            ChartKind::Manga => Manga::with(['genres', 'themes', 'media', 'mediaStat', 'translations', 'tv_rating']),
            ChartKind::People => Person::with(['media']),
            ChartKind::Songs => Song::with([]),
            ChartKind::Studios => Studio::with(['media'])
        };

        return $model->where('rank_total', '!=', 0)
            ->orderBy('rank_total')
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
