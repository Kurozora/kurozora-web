<?php

namespace App\Livewire\Components\Chart;

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
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
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
            ChartKind::Anime => Anime::with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
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
                    'user_watched_episodes as isWatched' => function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    }
                ]);
            }),
            ChartKind::Games => Game::with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                }),
            ChartKind::Manga => Manga::with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
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
