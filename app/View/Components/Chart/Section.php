<?php

namespace App\View\Components\Chart;

use App\Enums\ChartKind;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Section extends Component
{
    /**
     * The chart kind.
     *
     * @var string $chartKind
     */
    public string $chartKind;

    /**
     * The top ranked models of the chart kind.
     *
     * @var Collection $chart
     */
    public Collection $chart;

    /**
     * The URL that renders the section again.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create the component.
     *
     * @param string $chartKind
     */
    public function __construct(string $chartKind)
    {
        $this->chartKind = $chartKind;
        $this->chart = $this->loadChart();
        $this->refreshUrl = route('charts.section', $chartKind, false);
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.chart.section');
    }

    /**
     * Loads the top ranked models of the chart kind.
     *
     * @return Collection
     */
    protected function loadChart(): Collection
    {
        $model = match ($this->chartKind) {
            ChartKind::Anime => Anime::with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                }),
            ChartKind::Characters => Character::with(['media', 'translation']),
            ChartKind::Episodes => Episode::with([
                'anime' => function ($query) {
                    $query->with(['media', 'translation']);
                },
                'media',
                'season' => function ($query) {
                    $query->with(['translation']);
                },
                'translation'
            ])->when(auth()->user(), function ($query, $user) {
                $query->withExists([
                    'userWatchedEpisodes as isWatched' => function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->completed();
                    }
                ]);
            }),
            ChartKind::Games => Game::with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                }),
            ChartKind::Manga => Manga::with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                }),
            ChartKind::People => Person::with(['media']),
            ChartKind::Songs => Song::with(['media']),
            ChartKind::Studios => Studio::with(['media'])
        };

        return $model->where('rank_total', '!=', 0)
            ->orderBy('rank_total')
            ->limit(15)
            ->get();
    }
}
